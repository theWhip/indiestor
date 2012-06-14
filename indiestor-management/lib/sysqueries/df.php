<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('ShellQuery.php');

/*
Returns all device-hosted filesystems on the system. Example:

$ df -T -BG | grep -v tmpfs | tail -n +2
/dev/sda1      ext4          147G   12G      129G   8% /

All filesystems with 'tmpfs' mentioned in the type are eliminated.
The first header line is eliminated too.
*/

class DFFileSystem
{
	var $device=null;
	var $type=null;
	var $storageGB=null;
	var $usedGB=null;
	var $availableGB=null;
	var $percUse=null;
	var $mountedOn=null;
}

function sysquery_df()
{
	$dfFileSystems=array();
	$fileSystemLines=sysquery('df -T -BG | grep -v tmpfs | tail -n +2');
	$fileSystemArray=explode("\n",$fileSystemLines);
	foreach($fileSystemArray as $fileSystemLine)
	{
		if($fileSystemLine!='')
		{
			$fileSystemLine=preg_replace('/ +/',' ',$fileSystemLine);
			$fileSystemLineFields=explode(' ',$fileSystemLine);
			$dfFileSystem=new DFFileSystem();
			$dfFileSystem->device=$fileSystemLineFields[0];
			$dfFileSystem->type=$fileSystemLineFields[1];
			$dfFileSystem->storageGB=strip_last_char($fileSystemLineFields[2]);
			$dfFileSystem->usedGB=strip_last_char($fileSystemLineFields[3]);
			$dfFileSystem->availableGB=strip_last_char($fileSystemLineFields[4]);
			$dfFileSystem->percUse=strip_last_char($fileSystemLineFields[5]);
			$dfFileSystem->mountedOn=$fileSystemLineFields[6];
			$dfFileSystems[]=$dfFileSystem;
		}
	}
	return $dfFileSystems;
}

function strip_last_char($string)
{
	if(strlen($string)==0) return '';
	return substr($string,0,-1);
}

