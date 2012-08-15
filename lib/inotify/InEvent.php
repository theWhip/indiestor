<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class InEvent
{
	var $date=null;
	var $watchType=null;
	var $folderWatched=null;
	var $fsObject=null;
	var $events=null;
	var $pid=null;
	var $event=null;
	var $isDir=null;

	function __construct()
	{
		global $argv;
		$this->date=date(DATE_RFC822);
		$this->watchType=$argv[1];
		$this->folderWatched=trim($argv[2]);
		$this->fsObject=$argv[3];
		$this->events=$argv[4];
		$this->pid=getmypid();
		$this->analyzeEventFlags();
	}

	function analyzeEventFlags()
	{
		$this->isDir=false;

		$flags=explode(',',$this->events);
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
				terminate("second event flag is not IN_ISDIR: '$eventFlags'");
			}
		}
		else if(count($flags)>2)
		{
			terminate("Cannot handle more than two event flags: '$eventFlags'");
		}
	}

	function toString()
	{
		return "pid:{$this->pid} args: {$this->watchType} {$this->folderWatched} {$this->fsObject} {$this->events}";
	}

	function homeFolderForMXFFolder()
	{
		if(preg_match('|(.*)/Avid\\\\ MediaFiles/MXF|', $this->folderWatched, $matches))
			return 	trim($matches[1]);
		else terminate("Cannot determine home folder from MXF Folder '{$this->folderWatched}'");
	}

	function homeFolder()
	{
		switch($this->watchType)
		{
			case "MAIN": return $this->folderWatched;
			case "MXF": return homeFolderForMXFFolder();
			default: terminate("Unknown watch type '{$this->watchType}");
		}
	}
}

