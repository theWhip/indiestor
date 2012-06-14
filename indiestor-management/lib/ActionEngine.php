<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('actions/EntityType.php');
require_once('args/ProgramActions.php');
require_once('etcfiles/all.php');
require_once('syscommands/all.php');
require_once('sysqueries/all.php');

define('ERRNUM_USER_EXISTS_ALREADY',50);
define('ERRNUM_GROUP_EXISTS_ALREADY',51);
define('ERRNUM_GROUP_DOES_NOT_EXISTS',52);
define('ERRNUM_USER_DOES_NOT_EXIST',53);
define('ERRNUM_GROUP_DOES_NOT_EXIST',54);
define('ERRNUM_DUPLICATE_MEMBERSHIP',55);
define('ERRNUM_USER_NOT_MEMBER_OF_ANY_GROUP',56);
define('ERRNUM_CANNOT_ADD_INDIESTOR_SYSUSER',57);
define('ERRNUM_USERNAME_INVALID_CHARACTERS',58);
define('ERRNUM_GROUPNAME_INVALID_CHARACTERS',58);
define('ERRNUM_MOVE_HOME_CONTENT_WITHOUT_SET_HOME',59);
define('ERRNUM_CANNOT_MOVE_HOME_CONTENT_TO_EXISTING_FOLDER',60);
define('ERRNUM_CANNOT_MOVE_HOME_TO_NON_FOLDER',61);
define('ERRNUM_HOME_FOLDER_MUST_BE_ABSOLUTE_PATH',62);
define('ERRNUM_REMOVE_HOME_CONTENT_WITHOUT_DELETE',63);
define('ERRNUM_HOME_FOLDER_ALREADY_BELONGS_TO_USER',64);
<<<<<<< HEAD
=======
define('ERRNUM_VOLUME_DEVICE_CANNOT_FIND_UUID',65);
define('ERRNUM_VOLUME_CANNOT_FIND_DEVICE_NOR_UUID',66);
define('ERRNUM_QUOTA_ALREADY_ON_FOR_DEVICE',67);
define('ERRNUM_QUOTA_ALREADY_OFF_FOR_DEVICE',68);
define('ERRNUM_CANNOT_FIND_BLOCKSIZE_FOR_DEVICE',69);
define('ERRNUM_QUOTA_NOT_NUMERIC',70);
define('ERRNUM_REMOVE_USER_QUOTA_ON_DEVICE_QUOTA_NOT_ENABLED',71);
define('ERRNUM_QUOTA_ALREADY_REMOVED_FOR_DEVICE',72);
>>>>>>> removed the call to quotacheck

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

<<<<<<< HEAD
=======
	static function switchOnQuotaForDevice($device)
	{
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		self::validateFileSystem($fileSystem,$device);
		$mountPoint=$fileSystem->_2_fs_file; //mount point
		if(!$fileSystem->hasQuotaEnabled())
		{
			//enable quota on filesystem
			$fileSystem->enableQuota();		
			$etcFstab->writeFileSystem($fileSystem);
			syscommand_mount_remount($mountPoint);
		}
		//enable quota on mount point
		if(!file_exists("$mountPoint/quota.user"))
		{
			syscommand_touch("$mountPoint/quota.user");
		}
		syscommand_chmod_numeric("$mountPoint/quota.user",'600');
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
		if(file_exists("$mountPoint/quota.user"))
		{
			syscommand_rm("$mountPoint/quota.user");
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

>>>>>>> removed the call to quotacheck
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
