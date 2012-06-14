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
<<<<<<< HEAD
		$format1="%-10s %-7s %7s %7s %7s %2s  %-s\n";
		$format2="%-10s %-7s %7d %7d %7d %2d  %-s\n";
		printf($format1,'device','type','stor.GB','used.GB','av.GB','%','mount');
=======
		$format1="%-20s %-7s %-5s %7s %7s %10s %3s  %-s\n";
		$format2="%-20s %-7s %-5s %7d %7d %10d %3d  %-s\n";
		printf($format1,'device (in GB)','type','quota','total','used','avail','%','mounted on');
>>>>>>> added --volumes -show --volume -quota-on -quota-off
		$dfFileSystems=sysquery_df();
		if(count($dfFileSystems>0))
		{
			foreach($dfFileSystems as $dfFileSystem)
			{
				printf($format2,$dfFileSystem->device,
						$dfFileSystem->type,
<<<<<<< HEAD
=======
						$dfFileSystem->quotaYN,
>>>>>>> added --volumes -show --volume -quota-on -quota-off
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
}

