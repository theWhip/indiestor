<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

requireLibFile('admin/etcfiles/EtcIndiestorQuota.php');

class Volume extends EntityType
{

	static function quotaOn($commandAction)
	{
		$device=ProgramActions::$entityName;
		ActionEngine::failOnOpenVZ($device);
		self::checkIfQuotaPackageInstalled();
		self::checkValidCharactersInVolumeName($device);
		self::checkIfQuotaAlreadyOnForDevice($device);
		DeviceQuota::switchOn($device);
                EtcIndiestorQuota::addVolume($device);
	}

	static function quotaOff($commandAction)
	{
		$device=ProgramActions::$entityName;
		ActionEngine::failOnOpenVZ($device);
		self::checkIfQuotaPackageInstalled();
		self::checkValidCharactersInVolumeName($device);
		self::checkIfQuotaAlreadyOffForDevice($device);
		DeviceQuota::switchOff($device);
                EtcIndiestorQuota::removeVolume($device);
	}

	static function quotaRemove($commandAction)
	{
		$device=ProgramActions::$entityName;
		ActionEngine::failOnOpenVZ($device);
		self::checkIfQuotaPackageInstalled();
		self::checkValidCharactersInVolumeName($device);
		self::checkIfQuotaAlreadyRemovedForDevice($device);
		DeviceQuota::remove($device);
                EtcIndiestorQuota::removeVolume($device);
	}

	static function checkIfQuotaPackageInstalled()
	{
		if(!sysquery_which('setquota'))
		{
			ActionEngine::error('AE_ERR_VOLUME_QUOTA_PACKAGE_NOT_INSTALLED',array());
		}
	}

	static function checkValidCharactersInVolumeName($device)
	{
		if(!ActionEngine::isValidCharactersInVolume($device))
			ActionEngine::error('AE_ERR_VOLUME_INVALID_CHARACTERS',array('volume'=>$device));
	}

	static function checkIfQuotaAlreadyOnForDevice($device)
	{
		if(sysquery_quotaon_p($device)===true)
			ActionEngine::warning('AE_WARN_QUOTA_ALREADY_ON_FOR_VOLUME',array('volume'=>$device));
	}

	static function checkIfQuotaAlreadyOffForDevice($device)
	{
		if(sysquery_quotaon_p($device)===false)
			ActionEngine::warning('AE_WARN_QUOTA_ALREADY_OFF_FOR_VOLUME',array('volume'=>$device));
	}

	static function checkIfQuotaAlreadyRemovedForDevice($device)
	{
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		DeviceQuota::validateFileSystem($fileSystem,$device);
		if(!$fileSystem->hasQuotaEnabled())
			ActionEngine::warning('AE_WARN_QUOTA_ALREADY_REMOVED_FOR_VOLUME',array('volume'=>$device));
	}
}

