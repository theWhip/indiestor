<?php

/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
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

