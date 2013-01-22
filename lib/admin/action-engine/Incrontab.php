<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

define('INCRON_ARGS','$@ $# $%');
define('INCRON_MAIN_EVENTS','IN_ATTRIB,IN_CREATE,IN_DELETE,IN_MOVED_FROM,IN_MOVED_TO');
define('INCRON_MAIN_EVENTS_WATCH_IN_MODIFY_TOO',INCRON_MAIN_EVENTS.',IN_MODIFY');
define('INCRON_SCRIPT_EVENT_HANDLER_PATH', indiestor_BIN().'/indiestor-inotify');

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

                        #check for this border case
                        if(!SharingFolders::folderHasValidAVPfile("$homeFolder/$avidFolder"))
                                $eventsToWatch=INCRON_MAIN_EVENTS_WATCH_IN_MODIFY_TOO;
                        else $eventsToWatch=INCRON_MAIN_EVENTS;

			$userIncronLines.="$homeFolder/$folder".' '.$eventsToWatch.' '.
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

