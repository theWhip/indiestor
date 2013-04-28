<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

class DeviceAcl
{

        static function isEnabled($device)
        {
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		EtcFsTab::validateFileSystem($fileSystem,$device);
		return $fileSystem->hasAclEnabled();
        }

	static function switchOn($device)
	{
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		EtcFsTab::validateFileSystem($fileSystem,$device);
		$mountPoint=$fileSystem->_2_fs_file; //mount point
		if(!$fileSystem->hasAclEnabled())
		{
			//enable acl on filesystem
			$fileSystem->enableAcl();		
			$etcFstab->writeFileSystem($fileSystem);
                        syscommand_tune2fs_switch_on($device);
			syscommand_mount_remount($mountPoint);
		}
	}

	static function switchOff($device)
	{
		$etcFstab=EtcFsTab::instance();
		$fileSystem=$etcFstab->findFileSystemForDevice($device);
		EtcFsTab::validateFileSystem($fileSystem,$device);
		$mountPoint=$fileSystem->_2_fs_file; //mount point
		if($fileSystem->hasAclEnabled())
		{
			//disable acl on filesystem
			$fileSystem->disableAcl();		
			$etcFstab->writeFileSystem($fileSystem);
                        syscommand_tune2fs_switch_off($device);
			syscommand_mount_remount($mountPoint);
		}
	}
}

