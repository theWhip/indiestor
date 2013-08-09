<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

requireLibFile('admin/ShellCommand.php');
requireLibFile('admin/syscommands/incrontab.php');

class Incrontab
{
	static function generate()
	{
		syscommand_incrontab('/var/spool/indiestor IN_CREATE /usr/bin/indiestor-inotify');
	}
}

