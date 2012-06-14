<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Removes files. Example:

$ rm myfile.*

*/

function syscommand_rm($filePath)
{
	ShellCommand::exec_fail_if_error("rm $filePath");	
}

