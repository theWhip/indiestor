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

Adds a user to a group. Example:

$ usermod -a -G myfriends john

*/

function syscommand_usermod_aG($userName,$groupName)
{
	ShellCommand::exec_fail_if_error("usermod -a -G $groupName $userName");
}

/*

Sets all groups for a user. Example:

$ usermod -G myfriends john

*/

function syscommand_usermod_G($userName,$groupNames)
{
	ShellCommand::exec_fail_if_error("usermod -G $groupNames  $userName");
}

/*

Sets the password for a user. Example:

$ usermod --password john '343#&*'

*/

function syscommand_usermod_password($userName,$passwd)
{
	$cryptedPwd=crypt($passwd);
	ShellCommand::exec_fail_if_error("usermod --password '$cryptedPwd' $userName");
}

/*

Locks a user. Example:

$ usermod --lock john

*/

function syscommand_usermod_lock($userName)
{
	ShellCommand::exec_fail_if_error("usermod --lock $userName");
}

/*

Changes the home folder for a user. Example:

$ usermod --home /var/users/stor1 john

*/

function syscommand_usermod_home($userName,$homeFolder)
{
	ShellCommand::exec_fail_if_error("usermod --home $homeFolder $userName");
}

