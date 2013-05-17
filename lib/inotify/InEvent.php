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

        function unquoteArgs()
        {
		global $argv;
                foreach($argv as $i=>$arg)
                {
                        $myArg=$arg;
                        if(substr($myArg,0,1)=='"') $myArg=substr($myArg,1);
                        if(substr($myArg,-1)=='"') $myArg=substr($myArg,0,-1);
                        $myArg2='';
                        for($j=0;$j<strlen($myArg);$j++)
                                if($myArg[$j]!='\\') $myArg2.=$myArg[$j];
                        $argv[$i]=$myArg2;
                }                
        }

	function __construct()
	{
		global $argv;
                self::unquoteArgs();
#syslog_notice("after:".print_r($argv,true));

                //this is a bug in the commandline argument parsing somewhere ...
                if($argv[3]=='MediaFiles/MXF')
                {
                        $argv[2]=$argv[2].' '.$argv[3];
                        $argv[3]=$argv[4];
                        $argv[4]=$argv[5];
                }

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

