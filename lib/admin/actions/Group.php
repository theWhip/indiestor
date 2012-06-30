<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
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
		syscommand_delgroup($sysGroupName);
		EtcGroup::reset();
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
}

