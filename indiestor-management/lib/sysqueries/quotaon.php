<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('ShellQuery.php');

/*

Checks if quota are enabled on device. Example:

$ quotaon -p /dev/sda1

*/

function sysquery_quotaon_p($deviceOrMountPoint)
{
	$result=sysquery("quotaon -p $deviceOrMountPoint 2> /dev/null");
	if($result==null) return false;
	$search=preg_match_all('/off/',$result,$matches);
	if($search===false) return false;
	if($search>0) return false;
	$search=preg_match_all('/on/',$result,$matches);	
	if($search===false) return false;
	if($search==0) return false;
	return true;
}

