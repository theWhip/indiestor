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

Moves an filesystem object. Example:

$ mv /home/john/myfile.txt /home/john/backup

*/

function syscommand_mv($fromPath,$toPath)
{
	ShellCommand::exec_fail_if_error("mv $fromPath $toPath");
}

