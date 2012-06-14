<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

<<<<<<< HEAD
require_once('ShellCommand.php');

=======
>>>>>>> lots of fixes to quota support
/*

Expels a user from the system, killing all its sessions:

$ pkill -KILL -u carl

*/

function syscommand_pkill_u($userName)
{
	ShellCommand::exec("pkill -KILL -u $userName");
}

