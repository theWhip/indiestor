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

Removes files. Example:

$ rm myfile.*

*/

function syscommand_rm($filePath)
{
	ShellCommand::exec("rm -f $filePath");	
}

