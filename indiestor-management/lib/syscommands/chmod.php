<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('ShellCommand.php');

/*

Changes the permissions of a filesystem object. Example:

$ chmod 600 myfile.txt

*/

function syscommand_chmod_numeric($filePath,$permissions)
{
	ShellCommand::exec("chmod $permissions $filePath");
}

