<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class User extends EntityType
{
        static function add($commandAction)
        {
		$userName=ProgramActions::$entityName;
<<<<<<< HEAD
		//if user exists already, abort
		self::checkForDuplicateUser($userName);
		//if indiestor user exists already, abort
		self::checkForDuplicateIndiestorUser($userName);
		//now add the user
		//XXX watch out home directory must be added immediately
        	Shell::exec("adduser $userName");
=======
		//check if the username is the indiestor system user
		self::checkForIndiestorSysUserName($userName);	
		//if indiestor user exists already, abort
		self::checkForDuplicateIndiestorUser($userName);
		//user exists already
		$etcPasswd=EtcPasswd::instance();
		$isExistingUser=$etcPasswd->exists($userName);
		//now add the user
        	if(!$isExistingUser)
		{
			//XXX watch out home directory must be added immediately
			Shell::exec("adduser $userName");
		}
>>>>>>> added --user -set-passwd -lock -unlock
		//make sure indiestor user group exists
		self::ensureIndiestorGroupExists();
		//add user to indiestor user group
		self::shellExecAddUserToGroup(ActionEngine::indiestorUserGroup,$userName);
        }

	static function shellExecAddUserToGroup($groupName,$userName)
	{
		Shell::exec("usermod -a -G $groupName $userName");
	}

	static function ensureIndiestorGroupExists()
	{
                $etcGroup=EtcGroup::instance();
		if($etcGroup->indiestorGroup==null)
		{
	        	Shell::exec('addgroup '.ActionEngine::indiestorUserGroup);
			EtcGroup::reset();
	                $etcGroup=EtcGroup::instance();
		}
	}

<<<<<<< HEAD
	static function checkForDuplicateUser($userName)
	{
		$etcPasswd=EtcPasswd::instance();
		if($etcPasswd->exists($userName))
		{
			ActionEngine::error("user '$userName' exists already outside indiestor",
						ERRNUM_USER_EXISTS_ALREADY_OUTSIDE_INDIESTOR);
=======
	static function checkForIndiestorSysUserName($userName)
	{
		if(ActionEngine::isIndiestorSysUserName($userName))
		{
			ActionEngine::error("Cannot add '$userName' system user as indiestor user",
						ERRNUM_CANNOT_ADD_INDIESTOR_SYSUSER);
>>>>>>> added --user -set-passwd -lock -unlock
		}
	}

	static function checkForDuplicateIndiestorUser($userName)
	{
                $etcGroup=EtcGroup::instance();
		$indiestorGroup=$etcGroup->indiestorGroup;
<<<<<<< HEAD
=======
                if($indiestorGroup==null) return;
>>>>>>> added --user -set-passwd -lock -unlock
		if($indiestorGroup->findMember($userName)!=null)
		{
			ActionEngine::error("indiestor user '$userName' exists already",
						ERRNUM_USER_EXISTS_ALREADY);
		}
	}

	static function checkForValidUserName($userName)
	{
                $etcGroup=EtcGroup::instance();
		$indiestorGroup=$etcGroup->indiestorGroup;
<<<<<<< HEAD
=======
                if($indiestorGroup==null) return;
>>>>>>> added --user -set-passwd -lock -unlock
		if($indiestorGroup->findMember($userName)==null)
		{
			ActionEngine::error("indiestor user '$userName' does not exist",
						ERRNUM_USER_DOES_NOT_EXIST);
		}
	}

	static function checkForValidGroupName($groupName)
	{
                $etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroup($groupName);
		if($group==null)
		{
			ActionEngine::error("indiestor group '$groupName' does not exist",
						ERRNUM_GROUP_DOES_NOT_EXIST);
		}
	}

	static function	checkForDuplicateUserMembership($userName)
	{
                $etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroupForUserName($userName);
		if($group!=null)
		{
			$groupName=$group->name;
			ActionEngine::error("user '$userName' already member of group $groupName",
						ERRNUM_DUPLICATE_MEMBERSHIP);
		}
	}

        static function delete($commandAction)
        {
		$userName=ProgramActions::$entityName;
		//if user does not exists, abort
		self::checkForValidUserName($userName);	
		//now delete the user
		//XXX watch out home directory may have to be deleted too
		Shell::exec("deluser $userName");
        }

	static function addToGroup($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if user does not exists, abort
		self::checkForValidUserName($userName);	
		$groupName=$commandAction->actionArg;
		//if group does not exist, abort
		self::checkForValidGroupName($groupName);
		//if user already member of any group, abort
		self::checkForDuplicateUserMembership($userName);
		//add user to user group
		self::shellExecAddUserToGroup(ActionEngine::sysGroupName($groupName),$userName);
	}

        static function groupNamesForUserName($userName)
        {
                $groupNamesForUserName=shell_exec("id -nG $userName");
                if($groupNamesForUserName==null) return array();
                return explode(' ',$groupNamesForUserName);
        }

        function removeGroupNameFromGroupNames($groupNames,$groupNameToRemove)
        {
                $result=array();
                foreach($groupNames as $groupName)
                {
                        if(trim($groupName)!=$groupNameToRemove)
                                $result[]=trim($groupName);
                }
                return $result;
        }

	function newGroupNamesForUserName($userName,$groupNameToRemove)
	{
                $groupNamesForUserName=self::groupNamesForUserName($userName);
                $newGroupNamesForUserName=self::removeGroupNameFromGroupnames(
						$groupNamesForUserName,
						$groupNameToRemove); 
                return implode(',',$newGroupNamesForUserName);
	}

	static function removeFromGroup($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if user does not exists, abort
		self::checkForValidUserName($userName);	
		//if user is not member of a group, abort
                $etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroupForUserName($userName);
		if($group==null)
		{
			ActionEngine::error("user '$userName' is not member of any group",
						ERRNUM_USER_NOT_MEMBER_OF_ANY_GROUP);
		}
		//we calculate the new collection of groups to which the user belongs
		//by removing his existing group from the list
<<<<<<< HEAD
		$groupName=ActionEngine::sysGroupName($group->name);
		$groupNames=self::newGroupNamesForUserName($userName,$groupName);
=======
		$groupNameToRemove=ActionEngine::sysGroupName($group->name);
		$groupNames=self::newGroupNamesForUserName($userName,$groupNameToRemove);
		Shell::exec("usermod $userName -G $groupNames");
	}

	static function setPasswd($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if user does not exists, abort
		self::checkForValidUserName($userName);	
		$passwd=$commandAction->actionArg;
		$cryptedPwd=crypt($passwd);
		Shell::exec("usermod --password '$cryptedPwd' $userName");
	}

	static function lock($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if user does not exists, abort
		self::checkForValidUserName($userName);	
		Shell::exec("usermod --lock $userName");
	}

	static function unlock($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if user does not exists, abort
		self::checkForValidUserName($userName);	
		Shell::exec("usermod --unlock $userName");
	}

	static function removeFromIndiestor($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if user does not exists, abort
		self::checkForValidUserName($userName);	
		$groupNames=self::newGroupNamesForUserName($userName,ActionEngine::indiestorUserGroup);
>>>>>>> added --user -set-passwd -lock -unlock
		Shell::exec("usermod $userName -G $groupNames");
	}
}

