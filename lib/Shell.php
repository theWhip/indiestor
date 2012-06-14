<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class Shell
{
	function exec($command)
	{
		$command=$command.' 2>&1';
/*		echo "$command\n"; */
		$output=shell_exec($command);
		self::log($output);
	}

	function log($commandOutput)
	{
	}
}
