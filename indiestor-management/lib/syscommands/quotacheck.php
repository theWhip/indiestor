<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('ShellCommand.php');

/*

Build the quota files for a mountpoint. Example:

$ quotacheck /
quotacheck: Mountpoint (or device) / not found or has no quota enabled.
quotacheck: Cannot find filesystem to check or filesystem not mounted with quota option.

*/

function syscommand_quotacheck_mountpoint($mountPoint)
{
	ShellCommand::exec("quotacheck -fumg $mountPoint 2> /dev/null");
}

