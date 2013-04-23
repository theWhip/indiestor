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

Lists files. Example:

$ ls myfile.*
myfile.txt
myfile.bak

*/

function syscommand_ls($filePath)
{
	$output=ShellCommand::exec("ls $filePath 2> /dev/null");	
	echo $output;
}

