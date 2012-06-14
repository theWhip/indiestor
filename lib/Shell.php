<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('etcfiles/EtcGroup.php');
require_once('etcfiles/EtcPasswd.php');

class Shell
{

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
			EtcGroup::reset();
			EtcPasswd::reset();
		}
	}
}
