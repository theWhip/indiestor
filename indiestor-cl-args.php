<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class ProgramOptions
{
	static $groupsFilePath=null;
	static $previousGroupsFilePath=null;
	static $memberFoldersFilePath=null;
	static $simulation=null;
	static $verbose=null;
}

function processCommandLineArgs()
{
	global $argv;
	global $argc;

	$fileArgs=array();

	$i=0;
	foreach($argv as $arg)
	{
		$i++;
		if($i!=1)
		{
			if(substr($arg,0,1)=='-')
			{
				switch($arg)
				{
					case '-v': Shell::$verbose=true; 
						ProgramOptions:$verbose=true;
						break;
					case '-s': Shell::$simulation=true;
						ProgramOptions::$simulation=true;
						break;
					default: terminate("invalid option '$arg'");
				}
			}
			else
			{
				$fileArgs[]=$arg;
			}
		}
	}

	ProgramOptions::$groupsFilePath=dirname(__FILE__).'/groups.json';
	ProgramOptions::$previousGroupsFilePath=dirname(__FILE__).'/groups.previous.json';
	ProgramOptions::$memberFoldersFilePath=dirname(__FILE__).'/member-folders.json';

	switch(count($fileArgs))
	{
		case 0: break;
		case 1: ProgramOptions::$groupsFilePath=$fileArgs[0]; break;
		case 2: ProgramOptions::$groupsFilePath=$fileArgs[0]; 
			ProgramOptions::$previousGroupsFilePath=$fileArgs[1];
			break;
		case 3: ProgramOptions::$groupsFilePath=$fileArgs[0]; 
			ProgramOptions::$previousGroupsFilePath=$fileArgs[1];
			ProgramOptions::$memberFoldersFilePath=$fileArgs[2];
			break;
		default: terminate("invalid option '$arg'");
	}
}

function println($msg)
{
	echo "$msg\n";
}

function terminate($errMsg)
{
	println("$errMsg");
	usage();
	die("Program aborted.\n");
}

function usage()
{
	println('indiestor-config-sync '.
		'[-s] [-v] [groups.json] [groups.previous.json] [member-folders.json]');
	println('-v: verbose');
	println('-s: simulation only');
	println('you can specify other json files than the standard ones');
}

