<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class Volume extends EntityType
{
	static function quotaOn($commandAction)
	{
		$device=ProgramActions::$entityName;
		self::checkIfQuotaAlreadyOnForDevice($device);
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		self::validateFileSystem($fileSystem);
		if(!$etcFstab->fileSystemHasEtcFstabQuotaEnabled($fileSystem))
		{
			$etcFstab->fileSystemEnableEtcFstabQuota($fileSystem);		
		}
		$mountPoint=$fileSystem->_2_fs_file; //mount point
		ActionEngine::switchOnQuotaForMountPoint($mountPoint);
	}

	static function quotaOff($commandAction)
	{
		$device=ProgramActions::$entityName;
		self::checkIfQuotaAlreadyOffForDevice($device);
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		self::validateFileSystem($fileSystem);
		$mountPoint=$fileSystem->_2_fs_file; //mount point
		ActionEngine::switchOffQuotaForMountPoint($mountPoint);
	}

	static function checkIfQuotaAlreadyOnForDevice($device)
	{
		if(sysquery_quotaon_p($device))
			ActionEngine::error("Quota already on for device '$device'",
				ERRNUM_QUOTA_ALREADY_ON_FOR_DEVICE);
	}

	static function checkIfQuotaAlreadyOffForDevice($device)
	{
		if(!sysquery_quotaon_p($device))
			ActionEngine::error("Quota already off for device '$device'",
				ERRNUM_QUOTA_ALREADY_OFF_FOR_DEVICE);
	}
	
	static function validateFileSystem($fileSystem)
	{
		switch($fileSystem)
		{
			case 'no-uuid':	
				ActionEngine::error("Cannot find device '$device' in /etc/fstab. ".
					"Can also not find an UUID for this device",
					ERRNUM_VOLUME_DEVICE_CANNOT_FIND_UUID);
					break;
			case 'no-filesystem-for-uuid':
				ActionEngine::error("Cannot find device '$device' in /etc/fstab. ".
					"Can also not find a 'UUID=$deviceUUID' entry ".
					"in /etc/fstab for this device",
					ERRNUM_VOLUME_CANNOT_FIND_DEVICE_NOR_UUID);
					break;
		}		
	}
}

