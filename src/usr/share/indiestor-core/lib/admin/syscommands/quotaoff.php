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

Disables quota. Example:

$ quotaoff /

*/

function syscommand_quotaoff($mountPoint)
{
	ShellCommand::exec_fail_if_error("quotaoff --format=vfsold -u $mountPoint");
}

