<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

define('DECISION_IGNORE','IGNORE');
define('DECISION_SHARE','SHARE');
define('DECISION_RENAME','RENAME');
define('DECISION_UNSHARE','UNSHARE');
define('DECISION_DELAYED','DELAYED');

class RenameOperation
{
	var $from=null;
	var $to=null;
	
	function __construct($from,$to)
	{
		$this->from=$from;
		$this->to=$to;
	}
}

class FromToTuple
{
	var $IN_MOVED_FROM=null;
	var $IN_MOVED_TO=null;
}

class InotifyEventHandler
{
	var $queueFolder=null;

	var $watchType=null;
	var $folder=null;
	var $fsObject=null;
	var $event=null;
	var $isDir=null;

	function __construct($queueFolder)
	{
		$this->queueFolder=$queueFolder;
	}
	
	function analyzeEventFlags($eventFlags)
	{
		$this->isDir=false;

		$flags=explode(',',$eventFlags);
		$this->event=$flags[0];
		if(count($flags)==2)
		{
			$flag2=$flags[1];
			if($flag2=='IN_ISDIR')
			{
				$this->isDir=true;
			}
			else
			{
				$this->error("second event flag is not IN_ISDIR: '$eventFlags'");
			}
		}
		else if(count($flags)>2)
		{
			$this->error("Cannot handle more than two event flags: '$eventFlags'");
		}
	}

	function error($msg)
	{
		throw new Exception($msg);
	}

	function decision($watchType,$folder,$fsObject,$eventFlags)
	{
		//store arguments
		$this->watchType=$watchType;
		$this->folder=trim($folder);
		$this->fsObject=$fsObject;
		$this->analyzeEventFlags($eventFlags);

		switch($watchType)
		{
			case 'MAIN': return $this->decisionMain(); 
			case 'MXF': return $this->decisionMxf();
			case 'IMP': return $this->decisionImp();
			default: $this->error("unknown watch type '$watchType'");
		}
	}

	function decisionMxf()
	{
		//ignore if it is not a folder
		if(!$this->isDir) return DECISION_IGNORE;
		//whatever else happens, just repair the sharing structure
		else return DECISION_SHARE;
	}

	function endsWith($str, $needle)
	{
	   $length = strlen($needle);
	   return !$length || substr($str, - $length) === $needle;
	}

	function endsWithAny($str, $needles)
	{
		foreach($needles as $needle)
		{
			if(self::endsWith($str,$needle)) return true;
		}
		return false;
	}

	function decisionMain()
	{
		//ignore if it is not a folder
		if(!$this->isDir) return $this->makeDecision(DECISION_IGNORE);
		//if the folder ends in .copy, just repair
		if(self::endsWith($this->fsObject,'.copy')) return $this->makeDecision(DECISION_SHARE);

		$event=$this->event;

		//handle PROJECT FOLDER IN_CREATE
		if($this->isCurrentFsObjectProjectFolder() && $event=='IN_CREATE') return $this->makeDecision(DECISION_SHARE);
		//handle PROJECT FOLDER IN_ATTRIB
		if($this->isCurrentFsObjectProjectFolder() && $event=='IN_ATTRIB') return $this->makeDecision(DECISION_SHARE);
		//handle PROJECT FOLDER IN_DELETE
		if($this->isCurrentFsObjectProjectFolder() && $event=='IN_DELETE') return $this->makeDecision(DECISION_UNSHARE);

		//handle IN_MOVE_TO
		if($event=='IN_MOVED_TO') return $this->decisionMainInMovedFromTo();

		//handle IN_MOVE_FROM
		if($event=='IN_MOVED_FROM') return $this->decisionMainInMovedFromTo();

		//everything else can be ignored
		return $this->makeDecision(DECISION_IGNORE);
	}

	function makeDecision($decision)
	{
		if($decision!='DELAYED') $this->queueItemRemove();
		return $decision;
	}

	function isCurrentFsObjectProjectFolder()
	{
		return self::isProjectFolder($this->fsObject);
	}

	function isProjectFolder($folder)
	{
		return self::endsWith($folder,'.shared') || self::endsWith($folder,'.avid') ;
	}

	function queueItemFilePath()
	{
		if($this->watchType=='IMP')
		{
			$filename=$this->fsObject;
		}
		else
		{
			$filename=md5($this->folder);
		}
		$queueItemFilePath="$this->queueFolder/$filename";
		return $queueItemFilePath;
	}

	function queueItemAdd()
	{
		//we write the pending type
		$content=$this->event.','.$this->fsObject.','.$this->folder;
		file_put_contents($this->queueItemFilePath(),$content);
	}

	function queueItemRead()
	{
		$content=file_get_contents($this->queueItemFilePath());
		return explode(',',$content);
	}

	function queueItemRemove()
	{
		if(file_exists($this->queueItemFilePath()))
				unlink($this->queueItemFilePath());
	}

	function queuedFileExists()
	{
		//wait for a quarter of a second
		//for other processes to write the queue 
		time_nanosleep(0, 250000000);
		return file_exists($this->queueItemFilePath());
	}

	function decisionImp()
	{
		if($this->queuedFileExists())
		{
			//queued event waiting
			$queueItem=$this->queueItemRead();
			$eventQueued=$queueItem[0];
			$folderQueued=$queueItem[1];
			$this->queueItemRemove();
			if($this->isProjectFolder($folderQueued))
			{
				switch($eventQueued)
				{
					//for all practical purposes, it amounts to an IN_CREATE
					case 'IN_MOVED_TO': return $this->makeDecision(DECISION_SHARE);
					//for all practical purposes, it amounts to an IN_DELETE
					case 'IN_MOVED_FROM': return $this->makeDecision(DECISION_UNSHARE);
					default: $this->error("Event queued expected 'IN_MOVED_TO' nor 'IN_MOVED_FROM'.".
							"Read from queue: '$eventQueued'");
				}
			}
			else
			{
				//a non-project folder was moved into or out of the folder watched, ignore
				return $this->makeDecision(DECISION_IGNORE);
			}
		}
		else
		{
			//NO queued event waiting
			//this means that it was captured by an IN_MOVE_FROM or IN_MOVE_TO event
			return $this->makeDecision(DECISION_IGNORE);
		}
	}

	function validateQueuedEvent($eventExpected,$eventQueued)
	{
			if($eventQueued!=$eventExpected) 
				$this->error("Expected a queued '$eventExpected' but the queue contained '$eventQueued'");
	}

	function processMoveFromTo($fromToTuple)
	{
		$folderMovedFrom=$fromToTuple->IN_MOVED_FROM;
		$folderMovedTo=$fromToTuple->IN_MOVED_TO;

		if(self::isProjectFolder($folderMovedFrom))
		{
			if(self::isProjectFolder($folderMovedTo))
			{
				//a project folder was renamed into another project folder
				return new RenameOperation($folderMovedFrom,$folderMovedTo);			
			}
			else
			{
				//a project folder was renamed into a non-project folder
				return $this->makeDecision(DECISION_UNSHARE);			
			}
		}
		else
		{
			//the folder was renamed to a non-project folder
			if(self::isProjectFolder($folderMovedTo))
			{
				//a non-project folder was renamed into a project folder
				return $this->makeDecision(DECISION_SHARE);
			}
			else
			{
				//a non-project folder was renamed into a non-project folder
				return $this->makeDecision(DECISION_IGNORE);
			}
		}			
	}

	function oppositeEvent($event)
	{
		switch($event)
		{
			case 'IN_MOVED_TO': return 'IN_MOVED_FROM';
			case 'IN_MOVED_FROM': return 'IN_MOVED_TO';
			default: $this->error("Cannot determine opposite of event '$event'");
		}
	}

	function validateQueuedFolder($folderWatchedQueued)
	{
		$currentFolder=$this->folder;
		if($currentFolder!=$folderWatchedQueued)
			$this->error("Expected folder in queue '$currentFolder'. Folder in queue is '$folderWatchedQueued'");
	}

	function decisionMainInMovedFromTo()
	{
		$folderMoved=$this->fsObject;
		if($this->queuedFileExists())
		{
			$event=$this->event;
			$oppositeEvent=self::oppositeEvent($event);
			//queued event waiting
			$queueItem=$this->queueItemRead();
			$eventQueued=$queueItem[0];
			$this->validateQueuedEvent($oppositeEvent,$eventQueued);
			$folderMovedOpposite=$queueItem[1];
			$folderWatchedOpposite=$queueItem[2];
			$this->validateQueuedFolder($folderWatchedOpposite);
			$this->queueItemRemove();
			//process move from to
			$fromTo=new FromToTuple();
			$fromToTuple->$event=$folderMoved;
			$fromToTuple->$oppositeEvent=$folderMovedOpposite;
			return $this->processMoveFromTo($fromToTuple);
		}
		else
		{
			//queue the event
			$this->queueItemAdd();
			return $this->makeDecision(DECISION_DELAYED);
		}	
	}
}

