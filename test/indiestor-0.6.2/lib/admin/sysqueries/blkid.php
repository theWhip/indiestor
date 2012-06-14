<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*
Returns the uuid for a device. Example:

$blkid /dev/sda1
/dev/sda1: UUID="8330b9ed-a303-4330-afcb-d737ac719a7f" TYPE="ext4"

*/

function sysquery_blkid($device)
{
	$deviceLine=ShellCommand::query_fail_if_error("blkid $device");
	if($deviceLine=='') return null;
	$deviceFields=explode(' ',$deviceLine);
	$deviceUUIDfields=explode('=',$deviceFields[1]);
	$UUID=strip_quotes($deviceUUIDfields[1]); 
	return $UUID;
}

function strip_quotes($string)
{
	if(strlen($string)<2) return $string;
	if(substr($string,0,1)!='"') return $string;
	if(substr($string,-1)!='"') return $string;
	return substr($string,1,-1);
}

