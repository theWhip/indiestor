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
>>>>>>> removed the call to quotacheck
/*

Adds a group to the system. Example:

$ addgroup myfriends

*/

function syscommand_addgroup($groupName)
{
	ShellCommand::exec("addgroup $groupName");
}

