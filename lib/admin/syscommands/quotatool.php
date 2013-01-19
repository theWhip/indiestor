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

Sets the quota for a user

# quotatool -b -u alex -l 2048MB /dev/sda3

returns 0, if the setting was successful
returns other number, if not.

*/

function syscommand_quotatool($userName,$device,$quotaGB)
{
	if(sysquery_which('quotatool'))
	{
		$quotaMB=$quotaGB*1024;
		ShellCommand::exec_fail_if_error("quotatool -b -u $userName -q {$quotaMB}MB -l {$quotaMB}MB $device");	
	}
}

