<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Check if an executable is installed and reachable on the path

--- INSTALLED ---

# which cracklib-check ; echo $?
/usr/sbin/cracklib-check
0

--- NOT INSTALLED ---

# which cracklib-check3423 ; echo $?
1

So, the return code is sufficient to say if there is a match or not.

*/

function sysquery_which($executable)
{
	$result=ShellCommand::query("which '$executable'",true);
	if($result->returnCode==0) return true;
	else return false;
}

