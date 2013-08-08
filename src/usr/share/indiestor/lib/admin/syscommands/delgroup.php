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

Deletes a group from the system. Example:

$ delgroup myfriends

*/

function syscommand_delgroup($groupName)
{
	ShellCommand::exec_fail_if_error("delgroup $groupName");
}

