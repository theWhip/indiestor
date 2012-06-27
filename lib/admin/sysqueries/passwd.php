<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

check if user is locked. Example:

$ passwd -S john
john L 05/20/2012 0 99999 7 -1 --> L means locked
john P 01/26/2012 0 99999 7 -1 --> P means not locked
nothing --> means the user does not exist
*/

function sysquery_passwd_S_locked($userName)
{
	$result=ShellCommand::query("passwd -S $userName");
	//for the sake of the argument, let's agree that a non-existent user is considered not-locked
	if($result==null) return false;	
	$fields=explode(' ',$result);
	if($fields[1]=='L') return true;
	else return false;
}

