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

Copies a folder recursively preserving all atributes. Example:

$ cp /home/john /var/users/stor3

*/

function syscommand_cp_aR($fromPath,$toPath)
{
	ShellCommand::exec_fail_if_error("cp -aR $fromPath $toPath");
}

