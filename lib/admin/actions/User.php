<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
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

		if(ProgramActions::actionExists('delete')) self::validateDelete($userName);
		if(ProgramActions::actionExists('set-home')) self::validateSetHome($userName);
		if(ProgramActions::actionExists('set-group')) self::validateSetGroup($userName);
		if(ProgramActions::actionExists('unset-group')) self::validateUnsetGroup($userName);
		if(ProgramActions::actionExists('lock')) self::validateLock($userName);
		if(ProgramActions::actionExists('set-quota')) self::validateSetQuota($userName);
		if(ProgramActions::actionExists('remove-quota')) self::validateRemoveQuota($userName);
		if(ProgramActions::actionExists('set-passwd')) self::validateSetPasswd($userName);
		if(ProgramActions::actionExists('add-to-samba')) self::validateAddToSamba($userName);
		if(ProgramActions::actionExists('remove-from-samba')) self::validateRemoveFromSamba($userName);
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
		if(!ProgramActions::actionExists('set-passwd') && !ProgramActions::actionExists('lock'))
			ActionEngine::warning('AE_WARN_USER_NO_PASSWORD',array('userName'=>$userName));
	}

	static function validateDelete($userName)
	{
		if(ps_is_logged_in($userName) || sysquery_smbstatus_is_logged_in($userName))
			ActionEngine::error('AE_ERR_USER_DELETE_LOGGED_IN',array('userName'=>$userName));
	}

	static function validateSetHome($userName)
	{
		if(ps_is_logged_in($userName) || sysquery_smbstatus_is_logged_in($userName))
			ActionEngine::error('AE_ERR_USER_SET_HOME_LOGGED_IN',array('userName'=>$userName));

		$commandAction=ProgramActions::findByName('set-home');
		$homeFolder=$commandAction->actionArg;
		self::checkHomeFolderIsAbsolutePath($homeFolder);
		self::checkParentNewHomeIsFolder($userName,$homeFolder);
		self::checkNewHomeNotOwnedAlready($userName,$homeFolder);
		self::checkValidCharactersInFolderName($homeFolder);

		if(!ProgramActions::actionExists('move-home-content'))
			if(file_exists($homeFolder))
				if(!is_dir($homeFolder))
					ActionEngine::error('AE_ERR_USER_HOME_NOT_FOLDER',
						array('userName'=>$userName,'homeFolder'=>$homeFolder));
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
			ActionEngine::warning('AE_WARN_USER_NOT_MEMBER_OF_ANY_GROUP',
						array('userName'=>$userName));
			exit(0);
		}
	}

	static function validateLock($userName)
	{
		self::checkIfUserAlreadyLocked($userName);
	}

	static function validateAddToSamba($userName)
	{
		self::checkIfSambaInstalled();
	}

	static function validateRemoveFromSamba($userName)
	{
		self::checkIfSambaInstalled();
	}

	static function checkIfSambaInstalled()
	{
		if(!sysquery_which('smbpasswd'))
			ActionEngine::error('AE_ERR_USER_SAMBA_NOT_INSTALLED',array());
	}

	static function homeFolderForUser($userName)
	{
		$etcPasswd=EtcPasswd::instance();
		$user=$etcPasswd->findUserByName($userName);
		if($user==null) return null;
		else return $user->homeFolder;
	}	

	static function deviceForUser($userName)
	{
		//find user home folder
		$homeFolder=self::homeFolderForUser($userName);
		//find device for user home folder
		return sysquery_df_device_for_folder($homeFolder);
	}

	static function checkIfQuotaPackageInstalled()
	{
		if(!sysquery_which('setquota'))
			ActionEngine::error('AE_ERR_USER_QUOTA_PACKAGE_NOT_INSTALLED',array());
		if(!sysquery_which('quotatool'))
			ActionEngine::error('AE_ERR_USER_QUOTATOOL_PACKAGE_NOT_INSTALLED',array());
	}

	static function validateSetQuota($userName)
	{
		//check if quota package is installed
		self::checkIfQuotaPackageInstalled();
		//quota
		$commandAction=ProgramActions::findByName('set-quota');
		$GB=$commandAction->actionArg;
		self::checkForValidQuota($GB);
		//device for user
		$device=self::deviceForUser($userName);
		//fail on openVZ
		ActionEngine::failOnOpenVZ($device);
		//check if it worked
		$homeFolder=self::homeFolderForUser($userName);
		//do not exceed the maximum available for the device
		self::checkIfQuotaNotOverMaximumAvailable($userName,$homeFolder,$device,$GB);
		//make sure quota is enabled
		DeviceQuota::switchOn($device);
		self::checkQuotaSwitchedOn($device,$homeFolder);
	}

	static function checkIfQuotaNotOverMaximumAvailable($userName,$homeFolder,$device,$GB)
	{
		$dfFileSystem=sysquery_df_device($device);
		if($dfFileSystem==null) 
			ActionEngine::error('SYS_ERR_USER_QUOTA_VOLUME_DOES_NOT_EXIST',
						array('userName'=>$userName,'homeFolder'=>$homeFolder,'volume'=>$device));
		$maxGB=$dfFileSystem->storageGB;
		if($GB>$maxGB)
			ActionEngine::error('AE_ERR_USER_QUOTA_OVER_MAXSIZE_VOLUME',
						array('volume'=>$device,'maxGB'=>$maxGB,'quota'=>$GB));

	}

	static function cracklibActive()
	{
		//first check: package installed
		if(!sysquery_dpkg_get_selections('crack')) return false;

		//second check: check if the cracklib-check executable is installed
		if(!sysquery_which('cracklib-check')) return false;

		//third check: check if cracklib is /etc/pam.d/common-password
		if(!sysquery_grep('/etc/pam.d/common-password','cracklib')) return false;

		//all checks conclusive, cracklib is considered active
		return true;
	}

	static function validateSetPasswd($userName)
	{
		$commandAction=ProgramActions::findByName('set-passwd');
		$passwd=$commandAction->actionArg;
		if(self::cracklibActive())
		{
			$processOutput=sysquery_cracklib_check($passwd);
			if($processOutput->returnCode!=0)
			{
				$errmsg=trim($processOutput->stdout);
				$fields=explode(':',$errmsg);
				$countFields=count($fields);
				if($countFields>0)
					$cracklibErrMsg=trim($fields[count($fields)-1]);
				else
					$cracklibErrMsg='unknown';			
				ActionEngine::error('AE_ERR_USER_PASSWD_REJECTED_BY_CRACKLIB',
					array('passwd'=>$passwd,'cracklib-errmsg'=>$cracklibErrMsg));
			}
		}
	}

	static function validateRemoveQuota($userName)
	{
		//check if quota package is installed
		self::checkIfQuotaPackageInstalled();
		//device for user
		$device=self::deviceForUser($userName);
		//make sure it's on
		ActionEngine::failOnOpenVZ($device);
		if(sysquery_quotaon_p($device)!==true)
			ActionEngine::warning('AE_WARN_USER_REMOVE_QUOTA_ON_DEVICE_QUOTA_NOT_ENABLED',
						array('userName'=>$userName,'volume'=>$device));
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
		//set quota to infinite by default
		if(!ProgramActions::actionExists('set-quota'))
		{
			$device=self::deviceForUser($userName);
			if(sysquery_quotaon_p($device)===true)
				syscommand_quotatool($userName,$device,0);
		}
		//add samba user
		syscommand_smbpasswd_a($userName);
        }

        static function delete($commandAction)
        {
		$userName=ProgramActions::$entityName;
		syscommand_deluser($userName,ProgramActions::actionExists('remove-home'));
		EtcPasswd::reset();
		syscommand_pdbedit_delete($userName);
        }

	static function removeHome($commandAction)
	{
		/* handled in the delete action already */
	}

	static function reshareGroup($groupName)
	{
                EtcGroup::reset();
		$group=EtcGroup::instance()->findGroup($groupName);
		if($group!=null)
		{
			$members=EtcPasswd::instance()->findUsersForEtcGroup($group);
			SharingStructureDefault::reshare($group->name,$members);
			SharingStructureAvid::reshare($group->name,$members);
		}
	}

	static function setGroup($commandAction)
	{
		$userName=ProgramActions::$entityName;
		$groupName=$commandAction->actionArg;
		//if user already member of any group, remove him
		$oldGroupName=self::removeFromISGroup($userName);
		//reshare old group
		if($oldGroupName!=null) self::reshareGroup($oldGroupName);
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
		if($group==null) return null;
		//prepare unsharing of projects
		$renameOps=self::unshareMemberFromGroupBeforeUnsetGroup($userName);
		//we calculate the new collection of groups to which the user belongs
		//by removing his existing group from the list
		$groupNameToRemove=ActionEngine::sysGroupName($group->name);
		$groupNames=self::newGroupNamesForUserName($userName,$groupNameToRemove);
		syscommand_usermod_G($userName,$groupNames);
		//finalize unsharing of projects
		EtcPasswd::reset();
		EtcGroup::reset();
		self::unshareMemberFromGroupAfterUnsetGroup($userName,$renameOps,$group->name);
		return ActionEngine::indiestorGroupName($groupNameToRemove);
	}

	static function unshareMemberFromGroupBeforeUnsetGroup($userName)
	{
		$user=EtcPasswd::instance()->findUserByName($userName);
		$group=EtcGroup::instance()->findGroupForUserName($userName);
		//avid
		$renameOps=SharingStructureAvid::renameUserAvidProjects($user);
		$members=EtcPasswd::instance()->findUsersForEtcGroup($group);
		SharingStructureAvid::reshare($group->name,$members); //for archiving own folders
		SharingStructureAvid::archiveASPFolder($user); //archive 'Avid Shared Projects'
		return $renameOps;
	}

	static function unshareMemberFromGroupAfterUnsetGroup($userName,$renameOps,$groupName)
	{
		$user=EtcPasswd::instance()->findUserByName($userName);
		$group=EtcGroup::instance()->findGroup($groupName);
		//avid
		ActionEngine::regenerateIncrontab();			
		SharingStructureAvid::renameBackUserAvidProjects($user,$renameOps);
		$members=EtcPasswd::instance()->findUsersForEtcGroup($group);
		SharingStructureAvid::reshare($groupName,$members); //for purging invalid symlinks
		//default
		SharingStructureDefault::purgeProjectLinks(array($user));
	}

	static function unsetGroup($commandAction)
	{
		$userName=ProgramActions::$entityName;
		$oldGroupName=self::removeFromISGroup($userName);
	}

	static function setPasswd($commandAction)
	{
		$userName=ProgramActions::$entityName;
		$passwd=$commandAction->actionArg;
		syscommand_usermod_password($userName,$passwd);
		EtcPasswd::reset();
		syscommand_smbpasswd($userName,$passwd);
	}

	static function lock($commandAction)
	{
		$userName=ProgramActions::$entityName;
		syscommand_usermod_lock($userName);
		EtcPasswd::reset();
		syscommand_smbpasswd_d($userName);
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

	static function removeFromSamba($commandAction)
	{
		$userName=ProgramActions::$entityName;
		if(sysquery_pdbedit_user($userName)==null)
			ActionEngine::warning('AE_WARN_USER_ALREADY_REMOVED_FROM_SAMBA',array('userName'=>$userName));
		syscommand_pdbedit_delete($userName);
	}

	static function addToSamba($commandAction)
	{
		$userName=ProgramActions::$entityName;
		if(sysquery_pdbedit_user($userName)!=null)
		{
			ActionEngine::warning('AE_WARN_USER_ALREADY_ADDED_TO_SAMBA',array('userName'=>$userName));
		}
		else
		{
			if(!ProgramActions::actionExists('set-passwd') && !ProgramActions::actionExists('lock'))
				ActionEngine::warning('AE_WARN_USER_ADD_TO_SAMBA_NO_PASSWORD',array('userName'=>$userName));
			syscommand_smbpasswd_a($userName);
		}
	}

	static function setHomeSetSkelFolder($userName,$homeFolder)
	{
		$skelFolder=trim(ShellCommand::query("cat /etc/adduser.conf | grep 'SKEL=' | sed 's/.*SKEL=\\([^ #]*\\).*/\\1/'"));
		syscommand_cp_aR("$skelFolder/.",$homeFolder);
	}

	static function setHome($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//if the add action is present, the set-home action has already been executed
		if(ProgramActions::actionExists('add')) return;
		$homeFolder=$commandAction->actionArg;
		if(!file_exists($homeFolder))
		{
			syscommand_mkdir($homeFolder);
			self::setHomeSetSkelFolder($userName,$homeFolder);
		}
		syscommand_chown_R($homeFolder,$userName,$userName);
		syscommand_usermod_home($userName,$homeFolder);
		EtcPasswd::reset();
		EtcGroup::reset();
	}

	static function setQuota($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//quota
		$GB=$commandAction->actionArg;
		//find device for user
		$device=self::deviceForUser($userName);
		//set the quota
		syscommand_quotatool($userName,$device,$GB);
	}

	static function removeQuota($commandAction)
	{
		$userName=ProgramActions::$entityName;
		//find device for user
		$device=self::deviceForUser($userName);
		//set the quota to zero; which effectively removes the quota
		syscommand_quotatool($userName,$device,0);
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
			ActionEngine::error('AE_ERR_USER_QUOTA_NOT_NUMERIC',array('GB'=>$GB));
	}

	static function	checkQuotaSwitchedOn($device,$homeFolder)
	{
		$userName=ProgramActions::$entityName;
		if(sysquery_quotaon_p($device)!==true)
			ActionEngine::error('SYS_ERR_USER_QUOTA_CANNOT_SWITCH_ON_FOR_VOLUME',
					array('userName'=>$userName,'volume'=>$device,'homeFolder'=>$homeFolder));
	}

	static function checkParentNewHomeIsFolder($userName,$homeFolder)
	{
		$parentFolder=dirname($homeFolder);
		if(!is_dir($parentFolder))
			ActionEngine::error('AE_ERR_USER_PARENT_OF_HOME_NOT_FOLDER',
				array('userName'=>$userName,'parentFolder'=>$parentFolder,'homeFolder'=>$homeFolder));
	}

	static function checkNewHomeNotOwnedAlready($userName,$homeFolder)
	{
		$etcPasswd=EtcPasswd::instance();
		$otherUser=$etcPasswd->findUserByHomeFolder($homeFolder);
		if($otherUser==null) return; //nobody owns this folder as home folder
		$otherUserName=$otherUser->name;
		if($otherUserName==$userName) return; //the user already owns the folder; no problem
		ActionEngine::error('AE_ERR_USER_HOME_FOLDER_ALREADY_BELONGS_TO_OTHER_USER',
				array('userName'=>$userName,'homeFolder'=>$homeFolder,'otherUserName'=>$otherUserName));
	}

	static function checkHomeFolderIsAbsolutePath($homeFolder)
	{
		$userName=ProgramActions::$entityName;
		if(substr($homeFolder,0,1)!='/')
			ActionEngine::error('AE_ERR_USER_HOME_FOLDER_MUST_BE_ABSOLUTE_PATH',
				array('userName'=>$userName,'homeFolder'=>$homeFolder));
	}

	static function checkIfUserAlreadyLocked($userName)
	{
		if(sysquery_passwd_S_locked($userName))
			ActionEngine::warning('AE_WARN_USER_ALREADY_LOCKED',array('userName'=>$userName));
	}

	static function checkValidCharactersInUserName($userName)
	{
		if(!ActionEngine::isValidCharactersInName($userName))
			ActionEngine::error('AE_ERR_USER_INVALID_CHARACTERS',array('userName'=>$userName));
	}

	static function checkValidCharactersInFolderName($folderName)
	{
		if(!ActionEngine::isValidCharactersInFolderName($folderName))
			ActionEngine::error('AE_ERR_USER_HOME_FOLDER_INVALID_CHARACTERS',array('homeFolder'=>$folderName));
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
			ActionEngine::error('AE_ERR_USER_CANNOT_ADD_INDIESTOR_SYSUSER',array('userName'=>$userName));
	}

	static function checkForDuplicateIndiestorUser($userName)
	{
                $etcGroup=EtcGroup::instance();
		$indiestorGroup=$etcGroup->indiestorGroup;
                if($indiestorGroup==null) return;
		if($indiestorGroup->findMember($userName)!=null)
			ActionEngine::error('AE_ERR_USER_EXISTS_ALREADY',array('userName'=>$userName));
	}

	static function checkForValidUserName($userName)
	{
                $etcGroup=EtcGroup::instance();
		$indiestorGroup=$etcGroup->indiestorGroup;
                if($indiestorGroup==null) return;
		if($indiestorGroup->findMember($userName)==null)
			ActionEngine::error('AE_ERR_USER_DOES_NOT_EXIST',array('userName'=>$userName));
	}

	static function checkForValidGroupName($groupName)
	{
                $etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroup($groupName);
		if($group==null)
			ActionEngine::error('AE_ERR_USER_GROUP_DOES_NOT_EXIST',array('group'=>$groupName));
	}

	static function afterCommand()
	{
		if(ProgramActions::hasUpdateCommand())
		{
			//reshare group
			$userName=ProgramActions::$entityName;
                        EtcGroup::reset();
			$group=EtcGroup::instance()->findGroupForUserName($userName);
			if($group!=null) self::reshareGroup($group->name);
			ActionEngine::regenerateIncrontab();			
		}
	}
}

