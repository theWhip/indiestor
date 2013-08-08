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

Adds a user to the system. Example:

$ adduser --disabled-password --gecos "" --home /var/users/stor1 carl

-----------
WATCH OUT:
-----------
if the --disabled-password --gecos options are not added, this command
is so friendly to go in interactive mode and hang the process !!!

*/

function syscommand_adduser($userName,$homeFolder=null)
{
	if($homeFolder==null)
	{
		$homeFolderOption='';
		$noCreateHome='';
	}
	else
	{
		$homeFolderOption="--home $homeFolder";
		if(file_exists($homeFolder))
		{
			$noCreateHome="--no-create-home";
		}
		else
		{
			$noCreateHome='';
		}
	}

	ShellCommand::exec_fail_if_error(
		"adduser --shell /bin/false --disabled-password --gecos '' $homeFolderOption $noCreateHome $userName");
}

