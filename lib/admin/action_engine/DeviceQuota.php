<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

define('USER_QUOTA_FILE','quota.user');

class DeviceQuota
{
	static function switchOn($device)
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

	static function switchOff($device)
	{
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
				if($device='/dev/simfs')
				{
					ActionEngine::error("Cannot find device '$device' in /etc/fstab. ".
						"Can also not find a UUID for this device. ".
						"This is apparently a VPS running in a Virtuozzo container. ".
						"You may need to enable second-level (per-user) Virtuozzo quota at the VPS level",
						ERRNUM_VOLUME_DEVICE_CANNOT_FIND_UUID);
				}
				else
				{
					ActionEngine::error("Cannot find device '$device' in /etc/fstab. ".
						"Can also not find a UUID for this device.",
						ERRNUM_VOLUME_DEVICE_CANNOT_FIND_UUID);
				}
				break;
			case 'no-filesystem-for-uuid':
			case 'no-uuid':	
				if($device='/dev/simfs')
				{
					ActionEngine::error("Cannot find device '$device' in /etc/fstab. ".
						"Can also not find a 'UUID=$deviceUUID' entry ".
						"in /etc/fstab for this device.".
						"This is apparently a VPS running in a Virtuozzo container. ".
						"You may need to enable second-level (per-user) Virtuozzo quota at the VPS level",
						ERRNUM_VOLUME_CANNOT_FIND_DEVICE_NOR_UUID);
				}
				else
				{
					ActionEngine::error("Cannot find device '$device' in /etc/fstab. ".
						"Can also not find a 'UUID=$deviceUUID' entry ".
						"in /etc/fstab for this device",
						ERRNUM_VOLUME_CANNOT_FIND_DEVICE_NOR_UUID);
				}
				break;
		}		
	}

	static function endMountPointWithSlash($mountPoint)
	{
		if(strlen($mountPoint)==0)
		{
			ActionEngine::error("invalid mount point folder '$mountPoint'",
				ERRNUM_INVALID_MOUNT_POINT_FOLDER);			
		}
		if(substr($mountPoint,-1)=='/') return $mountPoint;
		return $mountPoint.'/';
	}
}

