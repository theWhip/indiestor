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

delete user

*/

function syscommand_pdbedit_delete($userName)
{
	if(sysquery_which('pdbedit'))
	{
		if(sysquery_pdbedit_user($userName)!=null)
			ShellCommand::exec_fail_if_error("pdbedit --delete --user $userName");
	}
}

