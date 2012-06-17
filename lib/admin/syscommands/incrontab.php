<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

refreshes the incrontab from a string. Example:

$ incrontab --remove
$ echo -e $allIncronLines | incrontab -

*/

function syscommand_incrontab($incronLines)
{
	ShellCommand::exec_fail_if_error("incrontab --remove");
	file_put_contents('/tmp/incrontab-tmp',$incronLines);
	ShellCommand::exec_fail_if_error("cat /tmp/incrontab-tmp | incrontab -");
	unlink('/tmp/incrontab-tmp');
}

