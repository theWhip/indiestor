<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

define('USER_QUOTA_FILE','quota.user');

class DeviceQuota
{
	static function switchOn($device)
	{
		ActionEngine::failOnOpenVZ($device);
		//don't bother if the quota is already on
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

	static function switchOff($device)
	{
		ActionEngine::failOnOpenVZ($device);
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		self::validateFileSystem($fileSystem,$device);
		$mountPoint=$fileSystem->_2_fs_file; //mount point
		//switch off quote for mount point
		syscommand_quotaoff($mountPoint);
	}

	static function remove($device)
	{
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		self::validateFileSystem($fileSystem,$device);
		self::switchOff($device);
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
				ActionEngine::error('SYS_ERR_VOLUME_CANNOT_FIND_UUID',array('volume'=>$device));
				break;
			case 'no-filesystem-for-uuid':
			case 'no-uuid':	
				ActionEngine::error('SYS_ERR_VOLUME_CANNOT_FIND_VOLUME_NOR_UUID',
					array('volume'=>$device,'uuid'=>$deviceUUID));
				break;
		}		
	}

	static function endMountPointWithSlash($mountPoint)
	{
		if(strlen($mountPoint)==0)
			ActionEngine::error('SYS_ERR_VOLUME_INVALID_MOUNTPOINT',array('mountPoint'=>$mountPoint));
		if(substr($mountPoint,-1)=='/') return $mountPoint;
		else return $mountPoint.'/';
	}
}

