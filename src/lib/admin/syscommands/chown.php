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

Changes the ownership of a folder recursively. Example:

$ chown -R john.john /var/users/stor2

*/

function syscommand_chown_R($folder,$userName,$groupName)
{
	ShellCommand::exec_fail_if_error("chown -R $userName.$groupName $folder");
}

