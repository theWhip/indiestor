<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

<<<<<<< HEAD
require_once('ShellQuery.php');

=======
>>>>>>> fixes to error messages; reorganized indiestor subfolders
/*

Returns all groups in which a user is member. Example:

$ id -nG erik
erik adm dialout cdrom plugdev lpadmin admin sambashare

*/

function sysquery_id_nG($userName)
{
	//group names for user name
	$groupNamesForUserName=sysquery("id -nG $userName");
        if($groupNamesForUserName==null) return array();
	return explode(' ',$groupNamesForUserName);
}

