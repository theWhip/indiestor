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

function sysquery_pdbedit_user($userName)
{
	$sambaUsers=sysquery_pdbedit_list($userName);
	if(array_key_exists($userName,$sambaUsers)) return $sambaUsers[$userName];
	else return null;
}


function sysquery_pdbedit_list($userName=null)
{
	$users=array();
	if(!sysquery_which('pdbedit')) return $users;
	if($userName!=null) $userClause="--user $userName"; else $userClause=''; 
	$result=ShellCommand::query("pdbedit --list --smbpasswd-style $userClause",true);
	if($result->returnCode!=0) return $users;
	
	$lines=explode("\n",$result->stdout);
	foreach($lines as $line)
	{
		if(trim($line)!='')
		{
			$fields=explode(':',$line);
			if(count($fields)>=5)
			{
				$user=array();
				$name=$fields[0];
				$flags=$fields[4];
				$user['name']=$name;
				$user['flags']=$flags;
				$user['sambaFlagArray']=sambaFlagArray($flags);
				$users[$name]=$user;
			}
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


