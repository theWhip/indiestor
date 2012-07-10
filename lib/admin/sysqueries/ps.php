<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Checks who is logged in:

$ ps -e -o user | sort | uniq
$ ps -e -o ruser | sort | uniq

*/

function ps_is_logged_in($userName)
{
	$result=ShellCommand::query("ps -e -o user | sort | uniq");
	$lines=explode("\n",$result);
	foreach($lines as $line)
	{
		$line=trim($line);
		if($line==$userName) return true;
	}

	$result=ShellCommand::query("ps -e -o ruser | sort | uniq");
	$lines=explode("\n",$result);
	foreach($lines as $line)
	{
		$line=trim($line);
		if($line==$userName) return true;
	}
	return false;	
}

