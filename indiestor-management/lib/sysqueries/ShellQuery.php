<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

function sysquery($commandString)
{
<<<<<<< HEAD
	return shell_exec($commandString);
=======
	if(ProgramOptions::$simulation || ProgramOptions::$verbose)
	{
		echo "-query-> $commandString\n";
	}

	$result=shell_exec($commandString);

	if(ProgramOptions::$simulation || ProgramOptions::$verbose)
	{
		echo "--result--> $result";
	}
	return $result;
>>>>>>> fixes to error messages; reorganized indiestor subfolders
}

