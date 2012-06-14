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

Creates a directory. Example:

$ mkdir /home/john/myfile

*/

function syscommand_mkdir($folder)
{
	ShellCommand::exec("mkdir $folder");
}

