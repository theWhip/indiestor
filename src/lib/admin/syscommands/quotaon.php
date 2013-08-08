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

Enables quota. Example:

$ quotaon -ug /

*/

function syscommand_quotaon($mountPoint)
{
	ShellCommand::exec_fail_if_error("quotaon --format=vfsold -u $mountPoint");
}

