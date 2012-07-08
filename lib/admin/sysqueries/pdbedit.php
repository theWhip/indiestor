<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Report on samba user records

$ pdbedit --list --smbpasswd-style

john2:1005:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX:[U          ]:LCT-00000000:
mark3:1013:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX:[U          ]:LCT-00000000:
ben:1006:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX:[U          ]:LCT-00000000:

*/

function sysquery_pdbedit_list()
{
	$users=array();
	if(!sysquery_which('pdbedit')) return $users;
	$result=ShellCommand::query("pdbedit --list --smbpasswd-style");	
	$lines=explode("\n",$result);
	foreach($lines as $line)
	{
		if(trim($line)!='')
		{
			$fields=explode(':',$line);
			$user=array();
			$name=$fields[0];
			$flags=$fields[4];
			$user['name']=$name;
			$user['flags']=$flags;
			$user['sambaFlagArray']=sambaFlagArray($flags);
			$users[$name]=$user;
		}
	}
	return $users;
}

function sambaFlagArray($flags)
{
	$individualFlags=array();
	$i=0;
	for($i=0;$i<strlen($flags);$i++)
	{
		$letter=$flags[$i];
		if($letter!='[' & $letter!=']') $individualFlags[$letter]=$letter;
	}
	return $individualFlags;
}

