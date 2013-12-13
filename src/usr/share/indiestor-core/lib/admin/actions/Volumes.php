<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

class Volumes extends EntityType
{

	static function noVolumes()
	{
		echo "no volumes\n";
	}

        static function show($commandAction)
        {
		$format1="%-50s %-10s %-5s %7s %7s %10s %5s  %-s\n";
		printf($format1,'device (in GB)','type','quota','total','used','avail','%used','mounted on');
		$dfFileSystems=sysquery_df();
		$zpools=sysquery_zpool_list();
		if(count($dfFileSystems>0))
		{
			$hasOutput=false;
			foreach($dfFileSystems as $dfFileSystem)
			{
				//for zfs only show pools, not user-level quota
				if($dfFileSystem->type=='zfs')
				{
					if(array_key_exists($zpools,$dfFileSystem->device))
					{
						$hasOutput=true;
						self::showLine($dfFileSystem);
					}
				}
				else
				{
					$hasOutput=true;
					self::showLine($dfFileSystem);
				}
			}

			if(!$hasOutput)
				self::noVolumes();
		}
		else
		{
			self::noVolumes();
		}
        }

	static function showLine($dfFileSystem)
	{
		$format2="%-50s %-10s %-5s %7d %7d %10d %5d  %-s\n";
		printf($format2,$dfFileSystem->device,
				$dfFileSystem->type,
				$dfFileSystem->quotaYN,
				$dfFileSystem->storageGB,
				$dfFileSystem->usedGB,
				$dfFileSystem->availableGB,
				$dfFileSystem->percUse,
				$dfFileSystem->mountedOn);
	}

	static function purgeFstabBackups($commandAction)
	{
		$glob='/etc/fstab.ba*';
		echo "purging ...\n";
		syscommand_ls($glob);
		syscommand_rm($glob);
	}
}

