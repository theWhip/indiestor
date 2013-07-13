<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

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

	static function startWatching($commandAction)
	{
		$ISGroupName=ProgramActions::$entityName;
		self::checkInvalidGroup($ISGroupName);
		$etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroup($ISGroupName);
		InotifyWait::startWatching($ISGroupName);
	}

	static function stopWatching($commandAction)
	{
		$ISGroupName=ProgramActions::$entityName;
		self::checkInvalidGroup($ISGroupName);
		$etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroup($ISGroupName);
		InotifyWait::stopWatching($ISGroupName);
	}

	static function showWatchProcesses($commandAction)
	{
		$ISGroupName=ProgramActions::$entityName;
		self::checkInvalidGroup($ISGroupName);
		$pids=InotifyWait::watchProcesses($ISGroupName);
		foreach($pids as $pid)
			echo "$pid\n";
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
		foreach(InotifyWatchFolders::watchesMain($group) as $folder)
			echo "$folder\n";
	}

	static function showWatchesAVP($group)
	{
		foreach(InotifyWatchFolders::watchesAVP($group) as $folder)
			echo "$folder\n";
	}

	static function afterCommand()
	{
		if(ProgramActions::hasUpdateCommand())
		{
			ActionEngine::restartWatching();			
		}
	}
}

