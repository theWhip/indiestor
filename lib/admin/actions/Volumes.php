<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class Volumes extends EntityType
{

	static function noVolumes()
	{
		echo "no volumes\n";
	}

        static function show($commandAction)
        {
		$format1="%-30s %-10s %-5s %7s %7s %10s %5s  %-s\n";
		$format2="%-30s %-10s %-5s %7d %7d %10d %5d  %-s\n";
		printf($format1,'device (in GB)','type','quota','total','used','avail','%used','mounted on');
		$dfFileSystems=sysquery_df();
		if(count($dfFileSystems>0))
		{
			foreach($dfFileSystems as $dfFileSystem)
			{
				printf($format2,$dfFileSystem->device,
						$dfFileSystem->type,
						$dfFileSystem->quotaYN,
						$dfFileSystem->storageGB,
						$dfFileSystem->usedGB,
						$dfFileSystem->availableGB,
						$dfFileSystem->percUse,
						$dfFileSystem->mountedOn);
			}
		}
		else
		{
			self::noVolumes();
		}
        }

	static function purgeFstabBackups($commandAction)
	{
		$glob='/etc/fstab.ba*';
		echo "purging ...\n";
		syscommand_ls($glob);
		syscommand_rm($glob);
	}
}

