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

Enables/Disables acl

Examples:
$ tune2fs -o acl /dev/sdb1  ==> enables ACL
$ tune2fs -o ^acl /dev/sdb1 ==> disables ACL

*/

function syscommand_tune2fs_switch_on($device) 
{
	ShellCommand::exec_fail_if_error("tune2fs -o acl $device");
}

function syscommand_tune2fs_switch_off($device) 
{
	ShellCommand::exec_fail_if_error("tune2fs -o ^acl $device");
}

