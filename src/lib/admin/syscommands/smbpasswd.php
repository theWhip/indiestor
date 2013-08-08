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

Add user

*/

function syscommand_smbpasswd_a($userName)
{
	if(sysquery_which('smbpasswd'))
	{
		if(sysquery_pdbedit_user($userName)==null)	
			ShellCommand::exec_fail_if_error("(echo '';echo '') | smbpasswd -s -a $userName");
	}
}

/*

set password + unlock user

*/

function syscommand_smbpasswd($userName,$passwd)
{
	if(sysquery_which('smbpasswd'))
	{
		if(sysquery_pdbedit_user($userName)!=null)	
		{
			ShellCommand::exec_fail_if_error("(echo '$passwd';echo '$passwd') | smbpasswd -s $userName ");
			ShellCommand::exec_fail_if_error("smbpasswd -e $userName ");
		}
	}
}

/*

lock user

*/

function syscommand_smbpasswd_d($userName)
{
	if(sysquery_which('smbpasswd'))
	{
		if(sysquery_pdbedit_user($userName)!=null)	
			ShellCommand::exec_fail_if_error("smbpasswd -d $userName");
	}
}

