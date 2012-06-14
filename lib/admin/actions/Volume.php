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
		self::checkValidCharactersInVolumeName($device);
		self::checkIfQuotaAlreadyOnForDevice($device);
		ActionEngine::switchOnQuotaForDevice($device);
	}

	static function quotaOff($commandAction)
	{
		$device=ProgramActions::$entityName;
		self::checkValidCharactersInVolumeName($device);
		self::checkIfQuotaAlreadyOffForDevice($device);
		ActionEngine::switchOffQuotaForDevice($device);
	}

	static function quotaRemove($commandAction)
	{
		$device=ProgramActions::$entityName;
		self::checkValidCharactersInVolumeName($device);
		self::checkIfQuotaAlreadyRemovedForDevice($device);
		ActionEngine::removeQuotaForDevice($device);
	}

	static function checkValidCharactersInVolumeName($device)
	{
		if(!ActionEngine::isValidCharactersInVolume($device))
		{
			ActionEngine::error("'$device' contains invalid characters",
						ERRNUM_VOLUMENAME_INVALID_CHARACTERS);
		}
	}

	static function checkIfQuotaAlreadyOnForDevice($device)
	{
		if(sysquery_quotaon_p($device)===true)
			ActionEngine::error("Quota already on for device '$device'",
				ERRNUM_QUOTA_ALREADY_ON_FOR_DEVICE);
	}

	static function checkIfQuotaAlreadyOffForDevice($device)
	{
		if(sysquery_quotaon_p($device)===false)
			ActionEngine::error("Quota already off for device '$device'",
				ERRNUM_QUOTA_ALREADY_OFF_FOR_DEVICE);
	}

	static function checkIfQuotaAlreadyRemovedForDevice($device)
	{
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		ActionEngine::validateFileSystem($fileSystem,$device);
		if(!$fileSystem->hasQuotaEnabled())
		{
			ActionEngine::error("Quota already removed for device '$device'",
				ERRNUM_QUOTA_ALREADY_REMOVED_FOR_DEVICE);
		}
	}
}

