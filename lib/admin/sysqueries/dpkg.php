<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Check search string for packages installed

--- INSTALLED ---

# dpkg --get-selections | grep crack ; echo $?
cracklib-runtime				install
libcrack2					install
libpam-cracklib					install
0

--- NOT INSTALLED ---

# dpkg --get-selections | grep crack341 ; echo $?
1

So, the return code is sufficient to say if there is a match or not.

*/

function sysquery_dpkg_get_selections($searchNeedle)
{
	$result=ShellCommand::query("dpkg --get-selections | grep '$searchNeedle'",true);
	if($result->returnCode==0) return true;
	else return false;
}

