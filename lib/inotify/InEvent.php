<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
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

        function fixArgs()
        {
		global $argv;
                foreach($argv as $i=>$arg)
                        $argv[$i]=preg_replace(array('/"/','/\\\\/','/\|/'),array('','',' '),$arg);;
        }

        function dumpArgs($status)
        {
		global $argv;
                $line="$status:";
                foreach($argv as $arg)
                        $line.=" =$arg=";
                syslog_notice($line);       
        }

	function __construct()
	{
		global $argv;
                global $argc;
#                self::dumpArgs('as received');
                self::fixArgs();
#                self::dumpArgs('as unquoted');

		$this->date=date(DATE_RFC822);
		$this->watchType=$argv[1];
		$this->folderWatched=$argv[2];
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
				terminate("second event flag is not IN_ISDIR: '{$this->events}'");
			}
		}
		else if(count($flags)>2)
		{
			terminate("Cannot handle more than two event flags: '{$this->events}'");
		}
	}

	function toString()
	{
		return "pid:{$this->pid} args: {$this->watchType} {$this->folderWatched} {$this->fsObject} {$this->events}";
	}

	function homeFolderForMXFFolder()
	{
		if(preg_match('|(.*)/Avid MediaFiles.*|', $this->folderWatched, $matches))
			return 	trim($matches[1]);
		else terminate("Cannot determine home folder from MXF Folder '{$this->folderWatched}'");
	}

        function homeFolderForUnprotectedFolder()
        {
                if(preg_match("|Avid Shared Projects|",$this->folderWatched))
                {
                        return dirname(dirname(dirname(dirname($this->folderWatched))));
                }
                else
                {
                        return dirname(dirname(dirname($this->folderWatched)));
                }
        }

	function homeFolder()
	{
		switch($this->watchType)
		{
			case "MAIN": return $this->folderWatched;
			case "PRJ": return dirname($this->folderWatched);
			case "MXF": return $this->homeFolderForMXFFolder();
                        case "UNPROTECTED": return $this->homeFolderForUnprotectedFolder();
			default: terminate("Unknown watch type '{$this->watchType}'");
		}
	}
}

