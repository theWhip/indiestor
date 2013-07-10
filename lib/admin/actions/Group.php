<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

requireLibFile('inotify/SharingFolders.php');

class Group extends EntityType
{
        static function add($commandAction)
        {
		$ISGroupName=ProgramActions::$entityName;
		//if name contains invalid characters, abort
		self::checkValidCharactersInGroupName($ISGroupName);
		//if group exists already, abort
		self::checkDuplicateGroup($ISGroupName);
		$sysGroupName=ActionEngine::sysGroupName($ISGroupName);
		syscommand_addgroup($sysGroupName);
		EtcGroup::reset();
        }

	static function checkValidCharactersInGroupName($groupName)
	{
		if(!ActionEngine::isValidCharactersInName($groupName))
			ActionEngine::error('AE_ERR_GROUP_INVALID_CHARACTERS',array('group'=>$groupName));
	}

	static function checkDuplicateGroup($ISGroupName)
	{
		$etcGroup=EtcGroup::instance();
		if($etcGroup->exists($ISGroupName))
			ActionEngine::error('AE_ERR_GROUP_EXISTS_ALREADY',array('group'=>$ISGroupName));
	}

        static function delete($commandAction)
        {
		$ISGroupName=ProgramActions::$entityName;
		//if group does not exists, abort
		self::checkInvalidGroup($ISGroupName);
		$sysGroupName=ActionEngine::sysGroupName($ISGroupName);
		//remember members
		$group=EtcGroup::instance()->findGroup($ISGroupName);
		$oldMembers=EtcPasswd::instance()->findUsersForEtcGroup($group);
		//delete group
		syscommand_delgroup($sysGroupName);
		EtcGroup::reset();
		//purge group links
		if($oldMembers!=null)
			foreach($oldMembers as $member)
				{
					SharingStructureDefault::purgeProjectLinks(array($member));
					SharingStructureAvid::purgeProjectLinks(array($member));
				}
        }

	static function checkInvalidGroup($ISGroupName)
	{
		$etcGroup=EtcGroup::instance();
		if(!$etcGroup->exists($ISGroupName))
			ActionEngine::error('AE_ERR_GROUP_DOES_NOT_EXISTS',array('group'=>$ISGroupName));
	}

	static function showMembers($commandAction)
	{
		$ISGroupName=ProgramActions::$entityName;
		self::checkInvalidGroup($ISGroupName);
		$etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroup($ISGroupName);

		$userReportRecords=new UserReportRecords($group->members);
		$userReportRecords->output();
	}

	static function showWatches($commandAction)
	{

		$ISGroupName=ProgramActions::$entityName;
		self::checkInvalidGroup($ISGroupName);
		$etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroup($ISGroupName);

		if(count($group->members)<2) return;

		$watchType=$commandAction->actionArg;

		switch($watchType)
		{
			case 'main': self::showWatchesMain($group); break;
			case 'avp': self::showWatchesAVP($group); break;
			default: ActionEngine::error(
					'AE_ERR_GROUP_WATCH_TYPE_DOES_NOT_EXISTS',
					array('watchType'=>$watchType));
		}
	}

	static function showWatchesMain($group)
	{
		foreach(self::watchesMain($group) as $folder)
			echo "$folder\n";
	}

	static function watchesMain($group)
	{
		$folders=array();
                foreach($group->members as $member)
		{
			$user=EtcPasswd::instance()->findUserByName($member);
			$folders=array_merge($folders,self::watchesMainUser($group,$user));
		}
		return $folders;
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

	static function generateTabWatchTree($folder)
	{
		$watchFolders=array();
              	$folder=preg_replace('/ /','\ ',$folder);
		$searchFilter="\\( ! -regex '.*/\..*' ".
			"-and ! -name 'resource.frk' ".
			"-and ! -regex '.*/Statistics' ".
			"-and ! -regex '.*/SearchData'  \\)";
		$folders=ShellCommand::query("find $folder -type d $searchFilter");
		$folders=explode("\n",$folders);
		foreach($folders as $folder)
		{
			$folder=trim($folder);
			if($folder!="")
			{
			      	$folder=preg_replace('/ /','\ ',$folder);
				$watchFolders[]=$folder;
			}
		}
		return $watchFolders;
	}

	static function watchesMainUser($group,$user)
	{
		$watchFolders=array();
		$homeFolder=$user->homeFolder;
		$watchFolders[]="$homeFolder";
		$avidFolders=SharingFolders::userAvidProjects($homeFolder);
		foreach($avidFolders as $avidFolder)
		{
			$folder=str_replace(' ','\ ',$avidFolder);
			$watchFolders[]="$homeFolder/$folder";
                        $sharedFolders=SharingFolders::userSubFolders("$homeFolder/$avidFolder/Shared");
                        foreach($sharedFolders as $sharedFolder)
			{
				$folder="$homeFolder/$avidFolder/Shared/$sharedFolder";
                                if(!is_link($folder))
				{
					$watchFolders=array_merge($watchFolders,self::generateTabWatchTree($folder));
				}
                                else
                                {
                                        $target=readlink($folder);
					if($target!==false && is_dir($target) && 
                                             self::isLocatedInValidHomeFolderOfGroupMember(
								$target,$user->name,$group->members))
                                        {
						$watchFolders=array_merge($watchFolders,
							self::generateTabWatchTree($target));
                                        }
                                }
			}
		}
	
		#watch 'Avid MediaFiles'
		if(file_exists("$homeFolder/Avid MediaFiles")) 
			$watchFolders[]="$homeFolder/Avid\ MediaFiles";

		#watch 'Avid MediaFiles/MXF'
		if(file_exists("$homeFolder/Avid MediaFiles/MXF")) 
			$watchFolders[]="$homeFolder/Avid\ MediaFiles/MXF";

		return $watchFolders;
	}

	static function showWatchesAVP($group)
	{
		foreach(self::watchesAVP($group) as $folder)
			echo "$folder\n";
	}

	static function watchesAVP($group)
	{
		$folders=array();
                foreach($group->members as $member)
		{
			$user=EtcPasswd::instance()->findUserByName($member);
			$folders=array_merge($folders,self::watchesAVPUser($group,$user));
		}
		return $folders;
	}

	static function watchesAVPUser($group,$user)
	{

		$watchFolders=array();
		$homeFolder=$user->homeFolder;
		$avidFolders=SharingFolders::userAvidProjects($homeFolder);
		foreach($avidFolders as $avidFolder)
                        if(!SharingFolders::folderHasValidAVPfile("$homeFolder/$avidFolder"))
			{
				$folder=str_replace(' ','\ ',$avidFolder);
				$watchFolders[]="$homeFolder/$folder";
			}
		return $watchFolders;
	}

	static function afterCommand()
	{
		if(ProgramActions::hasUpdateCommand())
		{
			ActionEngine::regenerateIncrontab();			
		}
	}
}

