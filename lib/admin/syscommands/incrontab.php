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
	$tmpIncrontab='/tmp/incrontab-tmp'.getmypid();
	ShellCommand::exec_fail_if_error("incrontab --remove");
	file_put_contents($tmpIncrontab,$incronLines);
	ShellCommand::exec_fail_if_error("cat $tmpIncrontab | incrontab -");
	if(file_exists($tmpIncrontab)) unlink($tmpIncrontab);
}

function syscommand_incrontab_list()
{
	return shell_exec("incrontab --list");	
}

function syscommand_incrontab_show()
{
	echo syscommand_incrontab_list();
}

