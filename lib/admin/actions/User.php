<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class User extends EntityType
{

	static function validateUpFront()
	{
		$userName=ProgramActions::$entityName;

		//unless the user is being added, he must always exist upfront
		if(!ProgramActions::actionExists('add'))
		{
			self::checkForValidUserName($userName);	
		}
		else
		{
			self::validateAddAction($userName);
		}

		if(ProgramActions::actionExists('set-home')) self::validateSetHome($userName);
		if(ProgramActions::actionExists('set-group')) self::validateSetGroup($userName);
		if(ProgramActions::actionExists('unset-group')) self::validateUnsetGroup($userName);
		if(ProgramActions::actionExists('lock')) self::validateLock($userName);
		if(ProgramActions::actionExists('move-home-content')) self::validateMoveHomeContent($userName);
		if(ProgramActions::actionExists('set-quota')) self::validateSetQuota($userName);
		if(ProgramActions::actionExists('remove-quota')) self::validateRemoveQuota($userName);
	}

	static function validateAddAction($userName)
	{
		self::checkForIndiestorSysUserName($userName);	
		self::checkForDuplicateIndiestorUser($userName);
		self::checkValidCharactersInUserName($userName);
		if(!ProgramActions::actionExists('set-home'))
		{
			self::checkParentNewHomeIsFolder($userName,"/home/$userName");
			self::checkNewHomeNotOwnedAlready($userName,"/home/$userName");
		}
		if(!ProgramActions::actionExists('set-passwd'))
		{
			ActionEngine::warning("Adding user without password will leave user account locked",
						WARNING_ADDING_USER_WITHOUT_PASSWORD);
		}
	}

	static function validateSetHome($userName)
	{
		$commandAction=ProgramActions::findByName('set-home');
		$homeFolder=$commandAction->actionArg;
		self::checkHomeFolderIsAbsolutePath($homeFolder);
		self::checkParentNewHomeIsFolder($userName,$homeFolder);
		self::checkNewHomeNotOwnedAlready($userName,$homeFolder);
		self::checkValidCharactersInFolderName($homeFolder);

		if(!ProgramActions::actionExists('move-home-content'))
			if(file_exists($homeFolder))
				if(!is_dir($homeFolder))
					ActionEngine::error("cannot set home content to '$homeFolder';".
					"it is not a folder",
					ERRNUM_CANNOT_MOVE_HOME_TO_NON_FOLDER);
	}

	static function validateSetGroup($userName)
	{
		$commandAction=ProgramActions::findByName('set-group');
		$groupName=$commandAction->actionArg;
		self::checkForValidGroupName($groupName);
	}

	static function validateUnsetGroup($userName)
	{
                $etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroupForUserName($userName);
		if($group==null)
		{
			ActionEngine::warning("user '$userName' is not member of any group",
						WARNING_USER_NOT_MEMBER_OF_ANY_GROUP);
		}
	}

	static function validateLock($userName)
	{
		self::checkIfUserAlreadyLocked($userName);
	}

	static function validateMoveHomeContent($userName)
	{
		$commandAction=ProgramActions::findByName('set-home');
		$homeFolder=$commandAction->actionArg;
		if(file_exists($homeFolder))
		{
			ActionEngine::error("cannot move home content to folder '$homeFolder';".
				"the folder exists already",
				ERRNUM_CANNOT_MOVE_HOME_CONTENT_TO_EXISTING_FOLDER);
		}
	}

	static function homeFolderForUser($userName)
	{
		$etcPasswd=EtcPasswd::instance();
		$user=$etcPasswd->findUserByName($userName);
		return $user->homeFolder;
	}	

	static function deviceForUser($userName)
	{
		//find user home folder
		$homeFolder=self::homeFolderForUser($userName);
		//find device for user home folder
		return sysquery_df_device_for_folder($homeFolder);
	}

	static function validateSetQuota($userName)
	{
		//quota
		$commandAction=ProgramActions::findByName('set-quota');
		$GB=$commandAction->actionArg;
		self::checkForValidQuota($GB);
		//device for user
		$device=self::deviceForUser($userName);
		//make sure quota is enabled
		ActionEngine::switchOnQuotaForDevice($device);
		//check if it worked
		$homeFolder=self::homeFolderForUser($userName);
		self::checkQuotaSwitchedOn($device,$device,$homeFolder);
	}

	static function validateRemoveQuota($userName)
	{
		//device for user
		$device=self::deviceForUser($userName);
		//make sure it's on
		if(sysquery_quotaon_p($device)!==true)
		{
			ActionEngine::warning("Cannot remove quota for user '$userName' on device '$device' for which quota are not enabled ",
						WARNING_REMOVE_USER_QUOTA_ON_DEVICE_QUOTA_NOT_ENABLED);
		}
	}

        static function add($commandAction)
        {
		$userName=ProgramActions::$entityName;
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
			}
			else
			{
				//leave to default
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

        static function delete($commandAction)
        {
		$userName=ProgramActions::$entityName;
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

	static function setGroup($commandAction)
	{
		$userName=ProgramActions::$entityName;
		$groupName=$commandAction->actionArg;
		//if user already member of any group, remove him
		self::removeFromISGroup($userName);
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

	static function removeFromISGroup($userName)
	{
                $etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroupForUserName($userName);
		if($group==null) return;
		//we calculate the new collection of groups to which the user belongs
		//by removing his existing group from the list
		$groupNameToRemove=ActionEngine::sysGroupName($group->name);
		$groupNames=self::newGroupNamesForUserName($userName,$groupNameToRemove);
		syscommand_usermod_G($userName,$groupNames);
		EtcGroup::reset();
	}

	static function unsetGroup($commandAction)
	{
		$userName=ProgramActions::$entityName;
		self::removeFromISGroup($userName);
	}

	static function setPasswd($commandAction)
	{
		$userName=ProgramActions::$entityName;
		$passwd=$commandAction->actionArg;
		syscommand_usermod_password($userName,$passwd);
		EtcPasswd::reset();
	}

	static function lock($commandAction)
	{
		$userName=ProgramActions::$entityName;
		syscommand_usermod_lock($userName);
		EtcPasswd::reset();
	}

	static function expel($commandAction)
	{
		$userName=ProgramActions::$entityName;
		syscommand_pkill_u($userName);
	}

	static function removeFromIndiestor($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//remove the user from his indiestor group first
		self::removeFromISGroup($userName);
		//remove him from indiestor too
		$groupNames=self::newGroupNamesForUserName($userName,ActionEngine::indiestorUserGroup);
		syscommand_usermod_G($userName,$groupNames);
		EtcGroup::reset();
	}

	static function setHome($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if the add action is present, the set-home action has already been executed
		if(ProgramActions::actionExists('add')) return;
		$homeFolder=$commandAction->actionArg;
		if(ProgramActions::actionExists('move-home-content'))
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
			if(!file_exists($homeFolder))
			{
				syscommand_mkdir($homeFolder);
				//http://superuser.com/questions/61611/how-to-copy-with-cp-to-include-hidden-files-and-hidden-directories-and-their-con
				syscommand_cp_aR('/etc/skel/.',$homeFolder);
				syscommand_chown_R($homeFolder,$userName,$userName);
			}
			else
			{
				syscommand_chown_R($homeFolder,$userName,$userName);
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

	static function setQuota($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//quota
		$GB=$commandAction->actionArg;
		//find device for user
		$device=self::deviceForUser($userName);
		//find the number of blocks for the GB of quota
		$blocks=ActionEngine::deviceGBToBlocks($device,$GB);
		//set the quota
		syscommand_setquota_u($device,$userName,$blocks);
	}

	static function removeQuota($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//find device for user
		$device=self::deviceForUser($userName);
		//set the quota to zero; which effectively removes the quota
		syscommand_setquota_u($device,$userName,0);
	}

	static function show($commandAction)
	{
		$userName=ProgramActions::$entityName;
		$userRecord=new UserReportRecord($userName);
		$userRecords=array();
		$userRecords[]=$userRecord;
		ActionEngine::printUserRecords($userRecords);
	}

	static function	checkForValidQuota($GB)
	{
		if(!is_numeric($GB))
		{
			ActionEngine::error("the number of GB specified '$GB' is not numeric",
						ERRNUM_QUOTA_NOT_NUMERIC);
		}
	}

	static function	checkQuotaSwitchedOn($device,$device,$homeFolder)
	{
		if(sysquery_quotaon_p($device)!==true)
		{
			ActionEngine::error("Cannot switch on quota for user '$userName' ".
						"on device '$device' for home folder '$homeFolder' ",
						ERRNUM_CANNOT_SWITCH_ON_QUOTA_FOR_DEVICE);
		}
	}

	static function checkParentNewHomeIsFolder($userName,$homeFolder)
	{
		$parentFolder=dirname($homeFolder);
		if(!is_dir($parentFolder))
			ActionEngine::error("The parent folder of '$homeFolder' is not a folder",
			ERRNUM_PARENT_OF_HOME_NOT_FOLDER);
	}

	static function checkNewHomeNotOwnedAlready($userName,$homeFolder)
	{
		$etcPasswd=EtcPasswd::instance();
		$otherUser=$etcPasswd->findUserByHomeFolder($homeFolder);
		if($otherUser==null) return; //nobody owns this folder as home folder
		$otherUserName=$otherUser->name;
		if($otherUserName==$userName) return; //the user already owns the folder; no problem
		ActionEngine::error("home folder '$homeFolder' already belongs".
			" to user '$otherUserName'",
			ERRNUM_HOME_FOLDER_ALREADY_BELONGS_TO_USER);
	}

	static function checkHomeFolderIsAbsolutePath($homeFolder)
	{
		if(substr($homeFolder,0,1)!='/')
		{
			ActionEngine::error("home folder '$homeFolder' must be absolute path".
					" (starting with a '/' character)",
					ERRNUM_HOME_FOLDER_MUST_BE_ABSOLUTE_PATH);
		}
	}

	static function checkIfUserAlreadyLocked($userName)
	{
		if(sysquery_passwd_S_locked($userName))
		{
			ActionEngine::warning("user '$userName' already locked",
					WARNING_USER_ALREADY_LOCKED);
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

	static function checkValidCharactersInFolderName($folderName)
	{
		if(!ActionEngine::isValidCharactersInFolderName($folderName))
		{
			ActionEngine::error("'$folderName' contains invalid characters",
						ERRNUM_FOLDERNAME_INVALID_CHARACTERS);
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

	static function afterCommand()
	{
		if(ProgramActions::actionExists('add') ||
				ProgramActions::actionExists('delete') ||
				ProgramActions::actionExists('set-home') ||
				ProgramActions::actionExists('remove-from-indiestor'))
			ActionEngine::regenerateIncrontab();			
	}
}

