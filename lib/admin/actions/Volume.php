<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

requireLibFile('admin/SrvIndiestorQuota.php');

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
                SrvIndiestorQuota::addVolume($device);
	}

	static function quotaOff($commandAction)
	{
		$device=ProgramActions::$entityName;
		ActionEngine::failOnOpenVZ($device);
		self::checkIfQuotaPackageInstalled();
		self::checkValidCharactersInVolumeName($device);
		self::checkIfQuotaAlreadyOffForDevice($device);
		DeviceQuota::switchOff($device);
                SrvIndiestorQuota::removeVolume($device);
	}

	static function quotaRemove($commandAction)
	{
		$device=ProgramActions::$entityName;
		ActionEngine::failOnOpenVZ($device);
		self::checkIfQuotaPackageInstalled();
		self::checkValidCharactersInVolumeName($device);
		self::checkIfQuotaAlreadyRemovedForDevice($device);
		DeviceQuota::remove($device);
                SrvIndiestorQuota::removeVolume($device);
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
		EtcFsTab::validateFileSystem($fileSystem,$device);
		if(!$fileSystem->hasQuotaEnabled())
			ActionEngine::warning('AE_WARN_QUOTA_ALREADY_REMOVED_FOR_VOLUME',array('volume'=>$device));
	}

	static function aclOn($commandAction)
	{
		$device=ProgramActions::$entityName;
		self::checkIfAclAlreadyOnForDevice($device);
		DeviceAcl::switchOn($device);
	}

	static function aclOff($commandAction)
	{
		$device=ProgramActions::$entityName;
		self::checkIfAclAlreadyOffForDevice($device);
		DeviceAcl::switchOff($device);
	}

	static function checkIfAclAlreadyOnForDevice($device)
	{
                if(DeviceAcl::isEnabled($device))            
			ActionEngine::warning('AE_WARN_ACL_ALREADY_ON_FOR_VOLUME',array('volume'=>$device));
	}

	static function checkIfAclAlreadyOffForDevice($device)
	{
                if(!DeviceAcl::isEnabled($device))            
			ActionEngine::warning('AE_WARN_ACL_ALREADY_OFF_FOR_VOLUME',array('volume'=>$device));
	}
}

