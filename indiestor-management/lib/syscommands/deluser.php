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
>>>>>>> added --volume -quota-remove --volumes -purge-fstab-backups
/*

Deletes a user from the system. Example:

$ deluser --remove-home carl

*/

function syscommand_deluser($userName,$removeHome=false)
{
	if($removeHome) $removeHomeOption='--remove-home';
	else $removeHomeOption='';
	ShellCommand::exec("deluser $removeHomeOption $userName");
}

