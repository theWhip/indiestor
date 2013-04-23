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

refreshes the incrontab from a string. Example:

$ incrontab --remove
$ echo -e $allIncronLines | incrontab -

*/

function syscommand_incrontab($incronLines)
{
	$tmpIncrontab='/tmp/incrontab-tmp'.getmypid();
	ShellCommand::exec_fail_if_error("incrontab -u ".indiestor_INUSER()." --remove");
	file_put_contents($tmpIncrontab,$incronLines);
	ShellCommand::exec_fail_if_error("cat $tmpIncrontab | incrontab -u ".indiestor_INUSER()." -");
	if(file_exists($tmpIncrontab)) unlink($tmpIncrontab);
}

function syscommand_incrontab_list()
{
	return ShellCommand::exec("incrontab -u ".indiestor_INUSER()." --list");	
}

function syscommand_incrontab_show()
{
	echo syscommand_incrontab_list();
}

