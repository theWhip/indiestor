<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Expels a user from the system, killing all its sessions:

$ pkill -KILL -u carl

*/

function syscommand_pkill_u($userName)
{
	ShellCommand::exec_fail_if_error("pkill -KILL -u $userName");
}

