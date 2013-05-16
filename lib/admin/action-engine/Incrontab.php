<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

define('INCRON_ARGS','"$@" "$#" "$%"');
//We only look at events that have side effects; and not events that amount to just reading data
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
					$tab.=self::generateTabForUser($member,$etcUser->homeFolder,$group->members);
                }

		//write the lines
		syscommand_incrontab($tab);
	}

        static function isLocatedInValidHomeFolderOfGroupMember($folder,$userName,$groupMembers)
        {
                foreach($groupMembers as $member)
                {
			$etcMember=EtcPasswd::instance()->findUserByName($member);
                        if(preg_match("|^{$etcMember->homeFolder}|",$folder))
                                return true;
                }
                return false;
        }

	static function generateTabForUser($userName,$homeFolder,$groupMembers)
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
                        {
                                # onlywatch if the sharing has not yet started
                                if(!is_dir("$homeFolder/$avidFolder/Shared"))
                                        $eventsToWatch=INCRON_MAIN_EVENTS_WATCH_IN_MODIFY_TOO;
                                else $eventsToWatch=INCRON_MAIN_EVENTS;
                        }
                        else
                        {
                                $eventsToWatch=INCRON_MAIN_EVENTS;
                        }
			$userIncronLines.="$homeFolder/$folder".' '.$eventsToWatch.' '.
				INCRON_SCRIPT_EVENT_HANDLER_PATH.' PRJ '.INCRON_ARGS."\n";
                        
                        #handle Shared folders
                        $sharedFolders=SharingFolders::userSubFolders("$homeFolder/$avidFolder/Shared");
                        foreach($sharedFolders as $sharedFolder)
                                if(!is_link("$homeFolder/$avidFolder/Shared/$sharedFolder"))
                                {
		                        $userIncronLines.="$homeFolder/$avidFolder/Shared/$sharedFolder".' '.
                                                        INCRON_MAIN_EVENTS.' '.
			                                INCRON_SCRIPT_EVENT_HANDLER_PATH.
                                                        ' UNPROTECTED '.INCRON_ARGS."\n";
                                }
                                else
                                {
                                        $target=readlink("$homeFolder/$avidFolder/Shared/$sharedFolder");
					if($target!==false && is_dir($target) && 
                                             self::isLocatedInValidHomeFolderOfGroupMember($target,$userName,$groupMembers))
                                        {
                                                $target=preg_replace('/ /','\ ',$target);
		                                $userIncronLines.="$target".' '.
                                                                INCRON_MAIN_EVENTS.' '.
			                                        INCRON_SCRIPT_EVENT_HANDLER_PATH.
       	                                                        ' UNPROTECTED '.INCRON_ARGS."\n";
                                        }
                                }
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

