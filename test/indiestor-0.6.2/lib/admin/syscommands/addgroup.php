<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Adds a group to the system. Example:

$ addgroup myfriends

*/

function syscommand_addgroup($groupName)
{
	ShellCommand::exec_fail_if_error("addgroup $groupName");
}

