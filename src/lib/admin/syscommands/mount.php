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

Remounts a moint point. Example:

$ mount -o remount /

*/

function syscommand_mount_remount($mountPoint)
{
	ShellCommand::exec_fail_if_error("mount -o remount $mountPoint");
}

