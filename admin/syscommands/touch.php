<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Touches a file. Example:

$ touch myfile.txt

*/

function syscommand_touch($filePath)
{
	ShellCommand::exec("touch $filePath");	
}

