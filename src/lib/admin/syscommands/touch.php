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

Touches a file. Example:

$ touch myfile.txt

*/

function syscommand_touch($filePath)
{
	ShellCommand::exec_fail_if_error("touch $filePath");	
}

