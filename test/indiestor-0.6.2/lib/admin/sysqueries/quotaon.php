<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Checks if quota are enabled on device. Example:

$ quotaon -p /dev/sda1

*/

function sysquery_quotaon_p($deviceOrMountPoint)
{
	/*
		--- return value ---
		true: 	user quota are enabled
		false: 	user quota are disabled
		null: 	error while trying to figure it out
	*/

	$result=ShellCommand::query("quotaon --format=vfsold -p $deviceOrMountPoint | grep user");
	//no result is not an error; it means that the quota system is not even active
	if($result==null) return false;
	//if the result says 'is off', the quota has been activated, but quota are disabled
	$search=preg_match_all('/is off/',$result,$matches);
	//if searching for 'is off' is invalid, it is actually an error
	//when not found, the result should be zero, not false.
	if($search===false) return null;
	//if the phrase 'is off' appears in the result, the user quota are disabled
	if($search>0) return false;
	//if the result says 'is on', quotas are enabled
	$search=preg_match_all('/is on/',$result,$matches);
	//if searching for 'is on' is invalid, it is actually an error
	//when not found, the result should be zero, not false.
	if($search===false) return null;
	//if the phrase 'is on' does not appear in the result, the user quota are disabled
	if($search==0) return false;
	//the phrase 'is on' does appear in the result, so the user quota are enabled
	return true;
}

