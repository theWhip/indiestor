<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Returns blocksize for a filesystem. Example:

$ dumpe2fs -hf /dev/sda1 | grep -i 'Block size' 
Block size:               4096

*/

function sysquery_dumpe2fs_blocksize($device)
{
	//-h flag to limit output to superblock information (otherwise the command is really slow)
	//-f to force output, even if there is potentially trouble with it
	$result=sysquery("dumpe2fs -hf $device 2> /dev/null | grep -i  'Block size'");
	$resultArray=explode(':',$result);
	$blockSize=trim($resultArray[1]);
	if(is_numeric($blockSize))
	{
		$blockSize=intval($blockSize);
		if($blockSize>0) return $blockSize;
		else return null; //error, zero or negative answer won't cut it
	}
	else
	{
		return null; //error, non-numeric answer won't cut it
	}
}

