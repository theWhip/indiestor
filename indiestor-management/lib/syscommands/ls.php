<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Lists files. Example:

$ ls myfile.*
myfile.txt
myfile.bak

*/

function syscommand_ls($filePath)
{
	$output=shell_exec("ls $filePath");	
	echo $output;
}

