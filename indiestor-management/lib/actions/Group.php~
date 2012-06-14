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
        }

	static function checkValidCharactersInGroupName($groupName)
	{
		if(!ActionEngine::isValidCharactersInName($groupName))
		{
			ActionEngine::error("'$groupName' contains invalid characters",
						ERRNUM_GROUPNAME_INVALID_CHARACTERS);
		}
	}

	static function checkDuplicateGroup($ISGroupName)
	{
		$etcGroup=EtcGroup::instance();
		if($etcGroup->exists($ISGroupName))
		{
			ActionEngine::error("group '$ISGroupName' exists already",
						ERRNUM_GROUP_EXISTS_ALREADY);
		}
	}

        static function delete($commandAction)
        {
		$ISGroupName=ProgramActions::$entityName;
		//if group does not exists, abort
		self::checkInvalidGroup($ISGroupName);
		$sysGroupName=ActionEngine::sysGroupName($ISGroupName);
		syscommand_delgroup($sysGroupName);
        }

	static function checkInvalidGroup($ISGroupName)
	{
		$etcGroup=EtcGroup::instance();
		if(!$etcGroup->exists($ISGroupName))
		{
			ActionEngine::error("group '$ISGroupName' does not exist",
						ERRNUM_GROUP_DOES_NOT_EXISTS);
		}
	}

	static function showMembers($commandAction)
	{
		$ISGroupName=ProgramActions::$entityName;
		self::checkInvalidGroup($ISGroupName);
		$etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroup($ISGroupName);
		foreach($group->members as $member)
		{
			echo "$member\n";
		}
	}
}

