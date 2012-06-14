<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Moves an filesystem object. Example:

$ mv /home/john/myfile.txt /home/john/backup

*/

function syscommand_mv($fromPath,$toPath)
{
	ShellCommand::exec_fail_if_error("mv $fromPath $toPath");
}

