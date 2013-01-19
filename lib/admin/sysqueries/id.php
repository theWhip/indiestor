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

Returns all groups in which a user is member. Example:

$ id -nG erik
erik adm dialout cdrom plugdev lpadmin admin sambashare

*/

function sysquery_id_nG($userName)
{
	//group names for user name
	$groupNamesForUserName=ShellCommand::query_fail_if_error("id -nG $userName");
        if($groupNamesForUserName==null) return array();
	return explode(' ',$groupNamesForUserName);
}

