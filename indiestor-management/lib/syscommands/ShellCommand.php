<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class ShellCommand
{

	static $simulation=false;
	static $verbose=false;

	function exec($command)
	{
		if(self::$simulation || self::$verbose)
		{
			echo "$command\n";
		}
<<<<<<< HEAD
		$command=$command.' 2>&1 &';
=======
		if(!self::$verbose) $command=$command.' 2>&1';
>>>>>>> removed the call to quotacheck
		if(!self::$simulation)
		{
			$output=shell_exec($command);
		}
	}
}
<<<<<<< HEAD
=======

>>>>>>> removed the call to quotacheck