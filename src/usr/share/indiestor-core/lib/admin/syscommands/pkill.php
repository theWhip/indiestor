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

Expels a user from the system, killing all its sessions:

$ pkill -KILL -u carl

*/

function syscommand_pkill_u($userName)
{
	ShellCommand::exec("pkill -KILL -u $userName");
}

