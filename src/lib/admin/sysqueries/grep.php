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

Check if a file contains a string

--- STRING CONTAINED ---

# cat /etc/pam.d/common-password | grep cracklib ; echo $?
password	requisite			pam_cracklib.so retry=3 minlen=8 difok=3
0

--- STRING NOT CONTAINED ---

# cat /etc/pam.d/common-password | grep cracklib12321 ; echo $?
1

So, the return code is sufficient to say if there is a match or not.

*/

function sysquery_grep($filePath,$needle)
{
	$result=ShellCommand::query("cat '$filePath' | grep '$needle'",true);
	if($result->returnCode==0) return true;
	else return false;
}

