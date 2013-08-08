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

Adds a group to the system. Example:

$ addgroup myfriends

*/

function syscommand_addgroup($groupName)
{
	ShellCommand::exec_fail_if_error("addgroup $groupName");
}

