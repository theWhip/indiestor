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
		//check if the username is the indiestor system user
		self::checkForIndiestorSysUserName($userName);	
		//if indiestor user exists already, abort
		self::checkForDuplicateIndiestorUser($userName);
		//if name contains invalid characters, abort
		self::checkValidCharactersInUserName($userName);
		//user exists already
		$etcPasswd=EtcPasswd::instance();
		$isExistingUser=$etcPasswd->exists($userName);
		//now add the user
        	if(!$isExistingUser)
		{
			if(ProgramActions::actionExists('set-home'))
			{
				$commandAction=ProgramActions::findByName('set-home');
				$homeFolder=$commandAction->actionArg;
				self::checkHomeFolderIsAbsolutePath($homeFolder);
				self::checkNewHomeNotOwnedAlready($userName,$homeFolder);
			}
			else
			{
				self::checkNewHomeNotOwnedAlready($userName,"/home/$userName");
				$homeFolder=null;
			}
			//execute
			syscommand_adduser($userName,$homeFolder);
			EtcPasswd::reset();
		}
		//make sure indiestor user group exists
		self::ensureIndiestorGroupExists();
		//add user to indiestor user group
		syscommand_usermod_aG($userName,ActionEngine::indiestorUserGroup);
		EtcPasswd::reset();
		EtcGroup::reset();
        }

	static function checkNewHomeNotOwnedAlready($userName,$homeFolder)
	{
		$etcPasswd=EtcPasswd::instance();
		$otherUser=$etcPasswd->findUserByHomeFolder($homeFolder);
		if($otherUser==null) return; //nobody owns this folder as home folder
		$otherUserName=$otherUser->name;
		ActionEngine::error("home folder $homeFolder already belongs".
			" to user $otherUserName",
			ERRNUM_HOME_FOLDER_ALREADY_BELONGS_TO_USER);
	}

	static function checkHomeFolderIsAbsolutePath($homeFolder)
	{
		if(substr($homeFolder,0,1)!='/')
		{
			ActionEngine::error("home folder must be absolute path".
					" (starting with a '/' character)",
					ERRNUM_HOME_FOLDER_MUST_BE_ABSOLUTE_PATH);
		}
	}

	static function checkValidCharactersInUserName($userName)
	{
		if(!ActionEngine::isValidCharactersInName($userName))
		{
			ActionEngine::error("'$userName' contains invalid characters",
						ERRNUM_USERNAME_INVALID_CHARACTERS);
		}
	}

	static function ensureIndiestorGroupExists()
	{
                $etcGroup=EtcGroup::instance();
		if($etcGroup->indiestorGroup==null)
		{
			syscommand_addgroup(ActionEngine::indiestorUserGroup);
			EtcGroup::reset();
	                $etcGroup=EtcGroup::instance();
		}
	}

	static function checkForIndiestorSysUserName($userName)
	{
		if(ActionEngine::isIndiestorSysUserName($userName))
		{
			ActionEngine::error("Cannot add '$userName' system user as indiestor user",
						ERRNUM_CANNOT_ADD_INDIESTOR_SYSUSER);
		}
	}

	static function checkForDuplicateIndiestorUser($userName)
	{
                $etcGroup=EtcGroup::instance();
		$indiestorGroup=$etcGroup->indiestorGroup;
                if($indiestorGroup==null) return;
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
                if($indiestorGroup==null) return;
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
		syscommand_deluser($userName,ProgramActions::actionExists('remove-home'));
		EtcPasswd::reset();
        }

	static function removeHome($commandAction)
	{
		//if the delete action is present, the remove-home action has already been executed
		if(ProgramActions::actionExists('delete')) return;
		ActionEngine::error("-remove-home only possible in -delete action",
						ERRNUM_REMOVE_HOME_CONTENT_WITHOUT_DELETE);
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
		syscommand_usermod_aG($userName,ActionEngine::sysGroupName($groupName));
		EtcGroup::reset();
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
                $groupNamesForUserName=sysquery_id_nG($userName);
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
		$groupNameToRemove=ActionEngine::sysGroupName($group->name);
		$groupNames=self::newGroupNamesForUserName($userName,$groupNameToRemove);
		syscommand_usermod_G($userName,$groupNames);
		EtcGroup::reset();
	}

	static function setPasswd($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if user does not exists, abort
		self::checkForValidUserName($userName);	
		$passwd=$commandAction->actionArg;
		syscommand_usermod_password($userName,$passwd);
		EtcPasswd::reset();
	}

	static function lock($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if user does not exists, abort
		self::checkForValidUserName($userName);	
		syscommand_usermod_lock($userName);
		EtcPasswd::reset();
	}

	static function expel($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if user does not exists, abort
		self::checkForValidUserName($userName);	
		syscommand_pkill_u($userName);
	}

	static function removeFromIndiestor($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if user does not exists, abort
		self::checkForValidUserName($userName);	
		$groupNames=self::newGroupNamesForUserName($userName,ActionEngine::indiestorUserGroup);
		syscommand_usermod_G($userName,$groupNames);
		EtcGroup::reset();
	}

	static function setHome($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if user does not exists, abort
		self::checkForValidUserName($userName);	
		//if the add action is present, the set-home action has already been executed
		if(ProgramActions::actionExists('add')) return;
		$homeFolder=$commandAction->actionArg;
		self::checkHomeFolderIsAbsolutePath($homeFolder);
		self::checkNewHomeNotOwnedAlready($userName,$homeFolder);
		if(ProgramActions::actionExists('move-home-content'))
		{
			if(!file_exists($homeFolder))
			{
				$etcPasswd=EtcPasswd::instance();
				$user=$etcPasswd->findUserByName($userName);
				$oldHomeFolder=$user->homeFolder;
				$userShell=$user->shell;
				//expel user
				syscommand_pkill_u($userName);
				//prevent login
				syscommand_chsh($userName,'/bin/false');
				//move the folder
				syscommand_mv($oldHomeFolder,$homeFolder);
				//reallow login
				syscommand_chsh($userName,$userShell);
			}
			else
			{
				ActionEngine::error("cannot move home content to folder $homeFolder;".
					"the folder exists already",
					ERRNUM_CANNOT_MOVE_HOME_CONTENT_TO_EXISTING_FOLDER);
			}
		}
		else
		{
			if(!file_exists($homeFolder))
			{
				syscommand_mkdir($homeFolder);
				syscommand_cp_aR('/etc/skel/*',$homeFolder);
				syscommand_chown_R($homeFolder,$userName,$userName);
			}
			else
			{
				if(!is_dir($homeFolder))
				{
					ActionEngine::error("cannot set home content to $homeFolder;".
					"it is not a folder",
					ERRNUM_CANNOT_MOVE_HOME_TO_NON_FOLDER);
				}
				else
				{
					syscommand_chown_R($homeFolder,$userName,$userName);
				}
			}
		}
		syscommand_usermod_home($userName,$homeFolder);
		EtcPasswd::reset();
		EtcGroup::reset();
	}

	static function moveHomeContent($commandAction)
	{
		//if the add action is present, the set-home action has already been executed
		if(ProgramActions::actionExists('set-home')) return;
		ActionEngine::error("-move-home-content only possible in -set-home action",
						ERRNUM_MOVE_HOME_CONTENT_WITHOUT_SET_HOME);
	}
}

