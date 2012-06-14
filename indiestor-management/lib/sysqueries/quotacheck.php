<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('ShellQuery.php');

/*

Checks if a mointpoint has quota enabled. Example:

$ quotacheck /
quotacheck: Mountpoint (or device) / not found or has no quota enabled.
quotacheck: Cannot find filesystem to check or filesystem not mounted with quota option.

quotacheck will exit with a non-zero return code if there are no quota enabled for the moint point.
shell_exec will return NULL in that case:
http://php.net/manual/en/function.shell-exec.php
"The output from the executed command or NULL if an error occurred".

*/

function sysquery_quotacheck_mountpoint($mountPoint)
{
	$result=sysquery("quotacheck -umg $mountPoint 2> /dev/null");
	if($result==null) return false;
	return true;
}

