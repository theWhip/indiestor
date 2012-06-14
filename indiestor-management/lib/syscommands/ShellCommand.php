<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class ShellCommand
{
<<<<<<< HEAD

	static $simulation=false;
	static $verbose=false;

	function exec($command)
	{
		if(self::$simulation || self::$verbose)
		{
			echo "$command\n";
		}
		$command=$command.' 2>&1 &';
		if(!self::$simulation)
		{
			$output=shell_exec($command);
		}
	}
}
=======
	function exec($command)
	{
		if(ProgramOptions::$simulation || ProgramOptions::$verbose)
		{
			echo "-exec-> $command\n";
		}
		if(!ProgramOptions::$verbose) $command=$command.' 2>&1';
		if(!ProgramOptions::$simulation)
		{
			$output=shell_exec($command);
		}
		if(ProgramOptions::$simulation || ProgramOptions::$verbose)
		{
			echo "-output--> $output";
		}
	}
}

>>>>>>> lots of fixes to quota support
