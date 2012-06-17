<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

define('INCRON_ARGS','$@ $# $%');
define('INCRON_MAIN_EVENTS','IN_ATTRIB,IN_CREATE,IN_DELETE,IN_MOVED_FROM,IN_MOVED_TO');
define('INCRON_OPTION_EVENTS','IN_DONT_FOLLOW,IN_ONLYDIR');
define('INCRON_SCRIPT_EVENT_HANDLER','indiestor-inotify');
define('INCRON_IN_MOVE_PENDING_FOLDER','in_move_pending');
define('INCRON_ROOT_FOLDER',dirname(dirname(dirname(__FILE__))));
define('INCRON_SCRIPT_EVENT_HANDLER_PATH','/usr/bin/php '.INCRON_ROOT_FOLDER.'/'.INCRON_SCRIPT_EVENT_HANDLER);
define('INCRON_IN_MOVE_PENDING_FOLDER_PATH',INCRON_ROOT_FOLDER.'/'.INCRON_IN_MOVE_PENDING_FOLDER);

class Incrontab
{
	static function generate()
	{
		//get indiestor users
		EtcGroup::reset();
                $etcGroup=EtcGroup::instance();
		$indiestorGroup=$etcGroup->indiestorGroup;
		if($indiestorGroup==null) $indiestorGroup=array();

		//watch in move pending
		$impIncronLine=INCRON_IN_MOVE_PENDING_FOLDER_PATH.' IN_CREATE '.
			INCRON_SCRIPT_EVENT_HANDLER_PATH.' IMP '.INCRON_ARGS."\n";

		$tab=$impIncronLine;

		EtcPasswd::reset();
		$etcPasswd=EtcPasswd::instance();

                foreach($indiestorGroup->members as $member)
                {
			$etcUser=$etcPasswd->findUserByName($member);
			$tab.=self::generateTabForUser($member,$etcUser->homeFolder);
                }

		//write the lines
		syscommand_incrontab($tab);
	}

	static function generateTabForUser($userName,$homeFolder)
	{
		#watch home folder
		$mainUserIncronLine=$homeFolder.' '.INCRON_MAIN_EVENTS.','.INCRON_OPTION_EVENTS.' '.
			INCRON_SCRIPT_EVENT_HANDLER_PATH.' MAIN '.INCRON_ARGS."\n";

		#watch mxf folder
		$mxfWatched="$homeFolder/Avid\ MediaFiles/MXF";
		$mxfUserIncronLine=$mxfWatched.' '.INCRON_MAIN_EVENTS.','.INCRON_OPTION_EVENTS.' '.
			INCRON_SCRIPT_EVENT_HANDLER_PATH.' MAIN '.INCRON_ARGS."\n";
		
		return $mainUserIncronLine.$mxfUserIncronLine;
	}
}

