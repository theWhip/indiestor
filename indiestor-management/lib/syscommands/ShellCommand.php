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
>>>>>>> added --volumes -show --volume -quota-on -quota-off
		if(!self::$simulation)
		{
			$output=shell_exec($command);
		}
	}
}
<<<<<<< HEAD
=======

>>>>>>> added --volumes -show --volume -quota-on -quota-off
