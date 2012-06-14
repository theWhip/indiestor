<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('ShellCommand.php');

/*

Adds a user to the system. Example:

$ adduser --home /var/users/stor1 carl

*/

function syscommand_adduser($userName,$homeFolder=null)
{
	if($homeFolder==null) $homeFolderOption='';
	else $homeFolderOption="--home $homeFolder";

	ShellCommand::exec("adduser $homeFolderOption $userName");
}

