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

Changes the ownership of a folder recursively. Example:

$ chown -R john.john /var/users/stor2

*/

function syscommand_chown_R($folder,$userName,$groupName)
{
	ShellCommand::exec("chown -R $userName.$groupName $folder");
}

