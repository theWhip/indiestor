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

Deletes a user from the system. Example:

$ deluser --remove-home carl

*/

function syscommand_deluser($userName,$removeHome=false)
{
	if($removeHome) $removeHomeOption='--remove-home';
	else $removeHomeOption='';
	ShellCommand::exec_fail_if_error("deluser $removeHomeOption $userName");
}

