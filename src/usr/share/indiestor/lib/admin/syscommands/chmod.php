<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

/*

Changes the permissions of a filesystem object. Example:

$ chmod 600 myfile.txt

*/

function syscommand_chmod_numeric($filePath,$permissions)
{
	ShellCommand::exec_fail_if_error("chmod $permissions $filePath");
}

