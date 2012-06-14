<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Creates a directory. Example:

$ mkdir /home/john/myfile

*/

function syscommand_mkdir($folder)
{
	ShellCommand::exec("mkdir $folder");
}

