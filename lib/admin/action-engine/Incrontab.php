<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

define('INCRON_ARGS','$@ $# $%');
define('INCRON_MAIN_EVENTS','IN_ATTRIB,IN_CREATE,IN_DELETE,IN_MOVED_FROM,IN_MOVED_TO');
define('INCRON_PROJ_EVENTS','IN_ACCESS,IN_OPEN,IN_CLOSE,IN_CREATE,IN_DELETE,IN_MOVED_FROM,IN_MOVED_TO');
define('INCRON_SCRIPT_EVENT_HANDLER','indiestor-inotify');
define('INCRON_ROOT_FOLDER',dirname(dirname(dirname(dirname(__FILE__)))));
define('INCRON_SCRIPT_EVENT_HANDLER_PATH','/usr/bin/php '.INCRON_ROOT_FOLDER.'/'.INCRON_SCRIPT_EVENT_HANDLER);

require_once(dirname(dirname(dirname(__FILE__))).'/inotify/SharingStructure.php');

class Incrontab
{
	static function generate()
	{
		//get indiestor users
		EtcGroup::reset();
                $etcGroup=EtcGroup::instance();
		$indiestorGroup=$etcGroup->indiestorGroup;
		if($indiestorGroup==null) $indiestorGroup=array();

		$tab='';
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
		$userIncronLines='';

		#watch home folder
		$userIncronLines.=$homeFolder.' '.INCRON_MAIN_EVENTS.' '.
			INCRON_SCRIPT_EVENT_HANDLER_PATH.' MAIN '.INCRON_ARGS."\n";

		#watch Avid project folders
		$userAvidProjects=SharingStructure::userAvidProjects($homeFolder);
		foreach($userAvidProjects as $userAvidProject)
		{
			$userIncronLines.="$homeFolder/$userAvidProject".' '.INCRON_PROJ_EVENTS.' '.
				INCRON_SCRIPT_EVENT_HANDLER_PATH.' PROJ '.INCRON_ARGS."\n";
		}
	
		#watch 'Avid MediaFiles'
		if(file_exists("$homeFolder/Avid MediaFiles"))
		{
			$userIncronLines.="$homeFolder/Avid\ MediaFiles".' '.INCRON_MAIN_EVENTS.' '.
				INCRON_SCRIPT_EVENT_HANDLER_PATH.' MXF '.INCRON_ARGS."\n";
		}

		#watch 'Avid MediaFiles/MXF'
		if(file_exists("$homeFolder/Avid MediaFiles/MXF"))
		{
			$userIncronLines.="$homeFolder/Avid\ MediaFiles/MXF".' '.INCRON_MAIN_EVENTS.' '.
				INCRON_SCRIPT_EVENT_HANDLER_PATH.' MXF '.INCRON_ARGS."\n";
		}
		
		return $userIncronLines;
	}
}

