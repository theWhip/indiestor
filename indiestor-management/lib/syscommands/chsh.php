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

Changes a user's shell. Example:

$ chsh --shell /bin/false john

*/

function syscommand_chsh($userName,$shell)
{
	ShellCommand::exec("chsh --shell $shell $userName");
}

