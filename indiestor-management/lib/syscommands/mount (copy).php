<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('ShellCommand.php');

/*

Remounts a moint point. Example:

$ mount -o remount /

*/

function syscommand_mount_remount($mountPoint)
{
	ShellCommand::exec("mount -o remount $mountPoint");
}

