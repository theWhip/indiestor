<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

retrieve quota report

# repquota -p -s /dev/sda1

# repquota -s /dev/sda1
*** Report for user quotas on device /dev/disk/by-uuid/eccc35b3-2e12-4ec5-a02a-a02a54238416
Block grace time: 7days; Inode grace time: 7days
                        Space limits                File limits
User            used    soft    hard  grace    used  soft  hard  grace
----------------------------------------------------------------------
root      --   3798M      0K      0K           203k     0     0       
daemon    --     60K      0K      0K              4     0     0       
man       --   3260K      0K      0K            344     0     0       
libuuid   --     24K      0K      0K              2     0     0       
syslog    --   8188K      0K      0K             20     0     0       
colord    --     12K      0K      0K              3     0     0       
lightdm   --   1224K      0K      0K             37     0     0       
avahi-autoipd --      4K      0K      0K              1     0     0       
speech-dispatcher --      4K      0K      0K              1     0     0       
erik      --   2741M      0K      0K          12446     0     0       
grapjas   --     56K      0K      0K             10     0     0       
rara      --     28K   2560M   2304M              5     0     0       
zombie    --     28K   8704M   7834M              5     0     0       

0K: means no quota

# repquota -s /dev/sda1 > /dev/null ; echo $?
0

Returns zero, in case of no errors

# repquota -s /dev/sda1 | tail -n +6 | awk '{ print  $1,$3,$5}'
root 3798M 0K
daemon 60K 0K
man 3260K 0K
libuuid 24K 0K
syslog 8188K 0K
colord 12K 0K
lightdm 1224K 0K
avahi-autoipd 4K 0K
speech-dispatcher 4K 0K
erik 2741M 0K
grapjas 56K 0K
rara 28K 2304M
zombie 28K 7834M


*/

function sysquery_repquota_for_user($device,$user)
{
	$userQuotas=sysquery_repquota($device);
	if($userQuotas==null) return null;
	if(!array_key_exists($user,$userQuotas)) return null;
	return $userQuotas[$user];
}

function sysquery_repquota($device)
{
	if(!sysquery_which('repquota')) return null;
	$result=ShellCommand::query("repquota -s $device | tail -n +6 | awk '{ print  $1,$3,$5}'",true);	
	if($result->returnCode!=0) return null;
	$lines=explode("\n",$result->stdout);

	foreach($lines as $line)
	{
		if(trim($line)!='')
		{
			$fields=explode(' ',$line);
			if(count($fields)>=3)
			{
				$user=array();
				$name=$fields[0];
				$user['name']=$name;
				$user['used']=$fields[1];
				$user['quota']=$fields[2];
				$users[$name]=$user;
			}
		}
	}
	return $users;	
}


