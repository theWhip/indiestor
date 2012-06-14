<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('actions/EntityType.php');
require_once('args/ProgramActions.php');
require_once(dirname(dirname(__FILE__)).'/common/etcfiles/all.php');
require_once('syscommands/all.php');
require_once('sysqueries/all.php');

define('ERRNUM_USER_EXISTS_ALREADY',50);
define('ERRNUM_GROUP_EXISTS_ALREADY',51);
define('ERRNUM_GROUP_DOES_NOT_EXISTS',52);
define('ERRNUM_USER_DOES_NOT_EXIST',53);
define('ERRNUM_GROUP_DOES_NOT_EXIST',54);
define('ERRNUM_USER_NOT_MEMBER_OF_ANY_GROUP',55);
define('ERRNUM_CANNOT_ADD_INDIESTOR_SYSUSER',56);
define('ERRNUM_USERNAME_INVALID_CHARACTERS',57);
define('ERRNUM_GROUPNAME_INVALID_CHARACTERS',58);
define('ERRNUM_MOVE_HOME_CONTENT_WITHOUT_SET_HOME',59);
define('ERRNUM_CANNOT_MOVE_HOME_CONTENT_TO_EXISTING_FOLDER',60);
define('ERRNUM_CANNOT_MOVE_HOME_TO_NON_FOLDER',61);
define('ERRNUM_HOME_FOLDER_MUST_BE_ABSOLUTE_PATH',62);
define('ERRNUM_REMOVE_HOME_CONTENT_WITHOUT_DELETE',63);
define('ERRNUM_HOME_FOLDER_ALREADY_BELONGS_TO_USER',64);
define('ERRNUM_VOLUME_DEVICE_CANNOT_FIND_UUID',65);
define('ERRNUM_VOLUME_CANNOT_FIND_DEVICE_NOR_UUID',66);
define('ERRNUM_QUOTA_ALREADY_ON_FOR_DEVICE',67);
define('ERRNUM_QUOTA_ALREADY_OFF_FOR_DEVICE',68);
define('ERRNUM_CANNOT_FIND_BLOCKSIZE_FOR_DEVICE',69);
define('ERRNUM_QUOTA_NOT_NUMERIC',70);
define('ERRNUM_REMOVE_USER_QUOTA_ON_DEVICE_QUOTA_NOT_ENABLED',71);
define('ERRNUM_QUOTA_ALREADY_REMOVED_FOR_DEVICE',72);
define('ERRNUM_INVALID_MOUNT_POINT_FOLDER',73);
define('ERRNUM_USER_ALREADY_LOCKED',74);
define('ERRNUM_VOLUMENAME_INVALID_CHARACTERS',75);
define('ERRNUM_FOLDERNAME_INVALID_CHARACTERS',76);

define('USER_QUOTA_FILE','quota.user');

class UserReportRecord
{
	var $userName=null;
	var $homeFolder=null;
	var $device=null;
	var $locked=null;
	var $groupName=null;
	var $hasQuotaRecord=null;
	var $quotaTotalGB=null;
	var $quotaUsedGB=null;
	var $quotaUsedPerc=null;

	function __construct($userName)
	{
		$this->userName=$userName;
		$etcPasswd=EtcPasswd::instance();
		$etcUser=$etcPasswd->findUserByName($userName);
		$this->homeFolder=$etcUser->homeFolder;
		//find device for user home folder
		$this->device=sysquery_df_device_for_folder($this->homeFolder);
		$this->locked=sysquery_passwd_S_locked($userName);
		$etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroupForUserName($userName);
		if($group==null) $this->groupName=null;
		else $this->groupName=$group->name;
		$quotaRecord=sysquery_quota_u($userName);
		if($quotaRecord!=null)
		{
			$this->hasQuotaRecord=true;
			$this->quotaTotalGB=ActionEngine::deviceBlocksToGB($this->device,$quotaRecord['quotaTotalBlocks']);
			$this->quotaUsedGB=ActionEngine::deviceBlocksToGB($this->device,$quotaRecord['quotaUsedBlocks']);
			$this->quotaUsedPerc=$quotaRecord['quotaUsedPerc'];
		}
		else
		{
			$this->hasQuotaRecord=false;
		}
	}
}

class ActionEngine
{
	const indiestorGroupPrefix='is_';
	const indiestorUserGroup='indiestor-users';
	const indiestorSysUserName='indiestor';

	static function printStdErr($msg)
	{
		file_put_contents('php://stderr',$msg);
	}

	static function error($msg,$errNum)
	{
		self::printStdErr("Error $errNum: $msg.\n");
		exit($errNum);
	}

	static function printUserRecords($userRecords)
	{
		$format1="%-10s %-20s %-7s %-10s %10s %10s %10s\n";
		$format2="%-10s %-20s %-7s %-10s %10s %10s %10s\n";
		printf($format1,'user','home','locked','group','quota','used','%avail');
		foreach($userRecords as $userRecord)
		{
			//locked
			if($userRecord->locked) $locked='Y';
			else $locked='N';

			//groupName
			if($userRecord->groupName==null) $groupName='(none)';
			else $groupName=$userRecord->groupName;

			//quota
			if($userRecord->hasQuotaRecord)
			{
				$quotaTotalGB=number_format($userRecord->quotaTotalGB,2).'G';
				$quotaUsedGB=number_format($userRecord->quotaUsedGB,2).'G';
				$quotaUsedPerc=number_format($userRecord->quotaUsedPerc,2).'%';
			}
			else
			{
				$quotaTotalGB='-';
				$quotaUsedGB='-';
				$quotaUsedPerc='-';
			}

			printf($format2,
				$userRecord->userName,
				$userRecord->homeFolder,
				$locked,
				$groupName,
				$quotaTotalGB,
				$quotaUsedGB,
				$quotaUsedPerc);  
		}
	}

	static function sysGroupName($indieStorGroupName)
	{
		return self::indiestorGroupPrefix.$indieStorGroupName;
	}

	static function isSysGroupIndiestorGroup($sysGroupName)
	{
		$lenISGPrefix=strlen(self::indiestorGroupPrefix);
                if(strlen($sysGroupName)>= $lenISGPrefix) 
			$prefix=substr($sysGroupName,0,$lenISGPrefix);
		else return false;
                if($prefix==self::indiestorGroupPrefix)
			return true;
		else return false;
	}

	static function isIndiestorSysUserName($userName)
	{
		return $userName==self::indiestorSysUserName;
	}

	static function indiestorGroupName($sysGroupName)
	{
		$lenISGPrefix=strlen(self::indiestorGroupPrefix);
		if(!self::isSysGroupIndiestorGroup($sysGroupName)) return '';
                return substr($sysGroupName,$lenISGPrefix);
	}

	static function isValidCharactersInName($name)
	{
		//a valid name must start with a letter
		//and be followed by a letter of a digit, a dash or an underscore
		return preg_match('/^[a-z][-a-z0-9_]*$/',$name);
	}

	static function isValidCharactersInVolume($volume)
	{
		//a valid volume may only contain the following characters 
		return preg_match('/^[-a-z0-9_\/]*$/',$volume);
	}

	static function isValidCharactersInFolderName($folder)
	{
		//a valid folder may only contain the following characters 
		return preg_match('/^[-a-z0-9_\/]*$/',$folder);
	}

	static function endMountPointWithSlash($mountPoint)
	{
		if(strlen($mountPoint)==0)
		{
			self::error("invalid mount point folder '$mountPoint'",
				ERRNUM_INVALID_MOUNT_POINT_FOLDER);			
		}
		if(substr($mountPoint,-1)=='/') return $mountPoint;
		return $mountPoint.'/';
	}

	static function switchOnQuotaForDevice($device)
	{
		//don't bother if the quota is already ons
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		self::validateFileSystem($fileSystem,$device);
              	if(sysquery_quotaon_p($device)) return;
		$mountPoint=$fileSystem->_2_fs_file; //mount point
		if(!$fileSystem->hasQuotaEnabled())
		{
			//enable quota on filesystem
			$fileSystem->enableQuota();		
			$etcFstab->writeFileSystem($fileSystem);
			syscommand_mount_remount($mountPoint);
		}
		//enable quota on mount point
		$mountPoint=self::endMountPointWithSlash($mountPoint);
		$userQuotaFile=$mountPoint.USER_QUOTA_FILE;
		if(!file_exists($userQuotaFile))
		{
			syscommand_touch($userQuotaFile);
			syscommand_chmod_numeric($userQuotaFile,'600');
			//without quotacheck, the user quota file may (or may not) be considered corrupt by quotaon
			syscommand_quotacheck_new_quota_file($mountPoint);
		}
		else
		{
			syscommand_quotacheck_existing_quota_file($mountPoint);
		}
		//we can finally switch on the quota system
		syscommand_quotaon($mountPoint);
	}

	static function switchOffQuotaForDevice($device)
	{
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		self::validateFileSystem($fileSystem,$device);
		$mountPoint=$fileSystem->_2_fs_file; //mount point
		//switch off quote for mount point
		syscommand_quotaoff($mountPoint);
	}

	static function removeQuotaForDevice($device)
	{
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		self::validateFileSystem($fileSystem,$device);
		self::switchOffQuotaForDevice($device);
		$mountPoint=$fileSystem->_2_fs_file; //mount point
		if($fileSystem->hasQuotaEnabled())
		{
			$fileSystem->disableQuota();
			$etcFstab->writeFileSystem($fileSystem);
			syscommand_mount_remount($mountPoint);
		}
		$mountPoint=self::endMountPointWithSlash($mountPoint);
		$userQuotaFile=$mountPoint.USER_QUOTA_FILE;
		if(file_exists($userQuotaFile))
		{
			syscommand_rm($userQuotaFile);
		}
	}

	static function validateFileSystem($fileSystem,$device)
	{
		switch($fileSystem)
		{
			case 'no-uuid':	
				self::error("Cannot find device '$device' in /etc/fstab. ".
					"Can also not find a UUID for this device",
					ERRNUM_VOLUME_DEVICE_CANNOT_FIND_UUID);
					break;
			case 'no-filesystem-for-uuid':
				self::error("Cannot find device '$device' in /etc/fstab. ".
					"Can also not find a 'UUID=$deviceUUID' entry ".
					"in /etc/fstab for this device",
					ERRNUM_VOLUME_CANNOT_FIND_DEVICE_NOR_UUID);
					break;
		}		
	}


	static function deviceBlockSize($device)
	{
		$blockSize=sysquery_dumpe2fs_blocksize($device);
		if($blockSize==null)
		{
			self::error("Cannot find block size for device '$device'",ERRNUM_CANNOT_FIND_BLOCKSIZE_FOR_DEVICE);
		}
		return $blockSize;
	}

	static function deviceGBToBlocks($device,$GB)
	{
		$blockSize=self::deviceBlockSize($device);
		$numBytesInGB=1024*1024*1024;
		$blocksInGB=$GB*$numBytesInGB/$blockSize;
		return $blocksInGB;
	}

	static function deviceBlocksToGB($device,$blocks)
	{
		$blockSize=self::deviceBlockSize($device);
		$numBytesInGB=1024*1024*1024;
		$totalNumBytes=$blocks*$blockSize;
		$GB=$totalNumBytes/$numBytesInGB;
		return $GB;
	}

        static function execute()
        {
                $className=self::actionCamelCaseName(ProgramActions::$entityType);
                $scriptName='actions/'.$className.'.php';
                require_once($scriptName);
                $className::execute();
        }

        static function actionCamelCaseName($actionName)
        {
                $stringParts=explode('-',$actionName);
                $stringParts2=array();
                foreach($stringParts as $stringPart)
                {
                        $stringParts2[]=ucfirst($stringPart);
                }
                $actionCameCaseName=implode('',$stringParts2);
                return $actionCameCaseName;
        }

        static function actionCamelCaseNameWithFirstLowerCase($actionName)
        {
                $stringParts=explode('-',$actionName);
                $stringParts2=array();
                $i=0;
                foreach($stringParts as $stringPart)
                {
                        if($i==0) $stringParts2[]=strtolower($stringPart);
                        else $stringParts2[]=ucfirst($stringPart);
                        $i++;
                }
                $actionCamelCaseNameWithFirstLowerCase=implode('',$stringParts2);
                return $actionCamelCaseNameWithFirstLowerCase;
        }
}

