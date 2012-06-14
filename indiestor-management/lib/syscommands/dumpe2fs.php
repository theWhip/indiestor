<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Dumps the blocksize for a filesystem. Example:

$ rm myfile.*

*/

function syscommand_rm($filePath)
{
	ShellCommand::exec("rm $filePath");	
}

