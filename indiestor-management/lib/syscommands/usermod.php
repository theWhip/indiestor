<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

<<<<<<< HEAD
require_once('ShellCommand.php');

=======
>>>>>>> fixes to error messages; reorganized indiestor subfolders
/*

Adds a user to a group. Example:

$ usermod -a -G myfriends john

*/

function syscommand_usermod_aG($userName,$groupName)
{
	ShellCommand::exec("usermod -a -G $groupName $userName");
}

/*

Sets all groups for a user. Example:

$ usermod -G myfriends john

*/

function syscommand_usermod_G($userName,$groupNames)
{
	ShellCommand::exec("usermod $userName -G $groupNames");
}

/*

Sets the password for a user. Example:

$ usermod --password john '343#&*'

*/

function syscommand_usermod_password($userName,$passwd)
{
	$cryptedPwd=crypt($passwd);
	ShellCommand::exec("usermod --password '$cryptedPwd' $userName");
}

/*

Locks a user. Example:

$ usermod --lock john

*/

function syscommand_usermod_lock($userName)
{
	ShellCommand::exec("usermod --lock $userName");
}

/*

Changes the home folder for a user. Example:

$ usermod --home /var/users/stor1 john

*/

function syscommand_usermod_home($userName,$homeFolder)
{
	ShellCommand::exec("usermod --home $homeFolder $userName");
}

