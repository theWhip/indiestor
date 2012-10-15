<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

define('INCRON_ARGS','$@ $# $%');
define('INCRON_MAIN_EVENTS','IN_ATTRIB,IN_CREATE,IN_DELETE,IN_MOVED_FROM,IN_MOVED_TO');
define('INCRON_SCRIPT_EVENT_HANDLER','indiestor-inotify');
define('INCRON_SCRIPT_EVENT_HANDLER_PATH','/usr/bin/php '.indiestor_BIN().'/'.INCRON_SCRIPT_EVENT_HANDLER);

requireLibFile('admin/etcfiles/all.php');
requireLibFile('inotify/SharingFolders.php');

class Incrontab
{
	static function generate()
	{
		//get indiestor users
		EtcGroup::reset();
                $etcGroup=EtcGroup::instance();
		$indiestorGroup=$etcGroup->indiestorGroup;
		if($indiestorGroup==null) $indiestorGroup=new stdClass;
		if(!property_exists($indiestorGroup,'members')) $indiestorGroup->members=array();

		$tab='';
		EtcPasswd::reset();
		$etcPasswd=EtcPasswd::instance();


                foreach($indiestorGroup->members as $member)
                {
			$etcUser=$etcPasswd->findUserByName($member);
			$group=$etcGroup->findGroupForUserName($member);
			//only watch member folders for members in group
			if($group!=null)
				//only watch member folders for groups with at least 2 members
				if(count($group->members>=2))
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

		#watch Avid folders
		$avidFolders=SharingFolders::userAvidProjects($homeFolder);
		foreach($avidFolders as $avidFolder)
		{
			$folder=str_replace(' ','\ ',$avidFolder);
			$userIncronLines.="$homeFolder/$folder".' '.INCRON_MAIN_EVENTS.' '.
				INCRON_SCRIPT_EVENT_HANDLER_PATH.' PRJ '.INCRON_ARGS."\n";			
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

