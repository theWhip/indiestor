<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Sets the user or group quota. Example:

# setquota -u john 100 200 0 0 /dev/sda1

*/

function syscommand_setquota_u($device,$userName,$blocks)
{
	syscommand_setquota($device,$userName,'-u',$blocks);
}

function syscommand_setquota($device,$name,$type,$blocks)
{
	$block_softlimit= $blocks;
	$block_hardlimit= floor($blocks*0.9);
	$inode_softlimit=0; //disabled, no quota
	$inode_hardlimit=0; //disabled, no quota
	ShellCommand::exec_fail_if_error("setquota --format=vfsold $type $name $block_softlimit $block_hardlimit ".
			"$inode_softlimit $inode_hardlimit $device");
}

