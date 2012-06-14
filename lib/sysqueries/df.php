<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
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
	$device=null;
	$type=null;
	$storageGB=null;
	$usedGB=null;
	$availableGB=null;
	$percUse=null;
	$mountedOn=null;
}

function sysquery_df()
{
	$fileSystems=sysquery('df -T -BG | grep -v tmpfs | tail -n +2');	
}

