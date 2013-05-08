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
	var $quotaYN=null;
        var $aclYN=null;
	var $storageGB=null;
	var $usedGB=null;
	var $availableGB=null;
	var $percUse=null;
	var $mountedOn=null;
}

function sysquery_df_device($device)
{
	$dfFileSystems=sysquery_df();
	if(array_key_exists($device,$dfFileSystems)) return $dfFileSystems[$device];
	else return null;
}

function sysquery_df()
{
	$dfFileSystems=array();
	$fileSystemLines=ShellCommand::query_fail_if_error('df -BG -T | grep -v tmpfs | tail -n +2');
	$fileSystemArray=explode("\n",$fileSystemLines);
	
	for($i=0; $i<count($fileSystemArray); $i++)
	{
		$fileSystemLine=$fileSystemArray[$i];

		if($fileSystemLine!='')
		{
			$fileSystemLine=preg_replace('/ +/',' ',$fileSystemLine);
			$fileSystemLineFields=explode(' ',$fileSystemLine);

			$dfFileSystem=new DFFileSystem();

			$dfFileSystem->device=$fileSystemLineFields[0];

			if(count($fileSystemLineFields)!=7)
			{
				//the row is spread over two lines
				//we now read the second line for the remainder of the fields
				$i++;
				$fileSystemLine=$fileSystemArray[$i];
				$fileSystemLine=preg_replace('/ +/',' ',$fileSystemLine);
				$fileSystemLineFields=explode(' ',$fileSystemLine);
			}

			$dfFileSystem->type=$fileSystemLineFields[1];
			$dfFileSystem->storageGB=strip_last_char($fileSystemLineFields[2]);
			$dfFileSystem->usedGB=strip_last_char($fileSystemLineFields[3]);
			$dfFileSystem->availableGB=strip_last_char($fileSystemLineFields[4]);
			$dfFileSystem->percUse=strip_last_char($fileSystemLineFields[5]);
			$dfFileSystem->mountedOn=$fileSystemLineFields[6];

			//check quota
			if($dfFileSystem->device=='/dev/simfs')
			{
				//openvz/virtuozzo unsupported
				$dfFileSystem->quotaYN='N';
			}
			else
			{
				$quotaEnabled=sysquery_quotaon_p($dfFileSystem->device);
				if($quotaEnabled===true) $dfFileSystem->quotaYN='Y'; 
				else if($quotaEnabled===false) $dfFileSystem->quotaYN='N';
				else $dfFileSystem->quotaYN='?'; 
			}

			$dfFileSystems[$dfFileSystem->device]=$dfFileSystem;
		}
	}
	return $dfFileSystems;
}

function strip_last_char($string)
{
	if(strlen($string)==0) return '';
	return substr($string,0,-1);
}

function sysquery_df_device_for_folder($folder)
{
	$fileSystemLine=ShellCommand::query_fail_if_error("df $folder | tail -n +2");	
	$fileSystemLine=preg_replace('/ +/',' ',$fileSystemLine);
	$fileSystemLineFields=explode(' ',$fileSystemLine);
	$device=$fileSystemLineFields[0];
	return $device;
}


