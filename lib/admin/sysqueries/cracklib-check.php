<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Check if cracklib accepts the password. Example:

-- ACCEPTED --

~# echo "^)&)*(hello2" | cracklib-check ; echo $?
^)&)*(hello2: OK
0

-- REJECTED --

 echo "hello2" | cracklib-check ; echo $?
hello2: it is based on a dictionary word
0

So, the answer must end in ': OK'

-- ACCEPTED --

# echo "^)&)*(hello2" | cracklib-check | grep ': OK' ; echo $?
^)&)*(hello2: OK
0

-- REJECTED --

# echo "hello2" | cracklib-check | grep ': OK'; echo $?
1


*/

function sysquery_cracklib_check($passwd)
{
	$processOutput1=ShellCommand::query("echo '$passwd' | cracklib-check | grep ': OK'",true);
	//if ok is found, return the result
	if($processOutput1->returnCode==0) return $processOutput1;
	//otherwise, retrieve the actual error message
	$processOutput2=ShellCommand::query("echo '$passwd' | cracklib-check",true);
	//send it on with the original error code
	$processOutput2->returnCode=$processOutput1->returnCode;
	return $processOutput2;
}

