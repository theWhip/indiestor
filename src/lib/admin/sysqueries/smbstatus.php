<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

/*

Checks who is logged in through samba:

# smbstatus --processes | tail -n +5 | awk '{ print $2}' | sort | uniq
carl
grace
mark

*/

$smbstatus_cached_users=null;

function sysquery_smbstatus_is_logged_in($userName)
{
	$users=sysquery_smbstatus_processes();
	return array_key_exists($userName,$users);
}

function sysquery_smbstatus_processes()
{
	global $smbstatus_cached_users;

	//check if we can serve from cache
	if($smbstatus_cached_users!=null)
		return $smbstatus_cached_users;

	$smbstatus_cached_users=array();

	if(!sysquery_which('smbstatus')) return $smbstatus_cached_users;

	$result=ShellCommand::query("smbstatus --processes | tail -n +5 | awk '{ print $2}' | sort | uniq",true);
	if($result->returnCode!=0) return $smbstatus_cached_users;

	$lines=explode("\n",$result->stdout);

	foreach($lines as $line)
	{
		$line=trim($line);
		if($line!='')
		{
			$smbstatus_cached_users[$line]=$line;
		}
	}

	return $smbstatus_cached_users;
}

