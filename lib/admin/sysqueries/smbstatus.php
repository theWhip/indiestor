<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Checks who is logged in through samba:

$ smbstatus --user=erik | grep erik | awk '{ print $2 }'

*/

function smbstatus_is_logged_in($userName)
{
	$result=trim(ShellCommand::query("smbstatus --user=$userName | grep $userName | awk '{ print $2 }'"));
	if($result==$userName) return true;
	else return false;
}

