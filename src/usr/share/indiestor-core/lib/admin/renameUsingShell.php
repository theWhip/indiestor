<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

// https://bugs.php.net/bug.php?id=54097
// https://bugs.launchpad.net/ubuntu/+source/php5/+bug/723330
// [1] rename(): The first argument to copy() function cannot be a directory
// [2] ZFS-related: rename($a,$b): Invalid cross-device link

function renameUsingShell($from,$to)
{
	shell_exec("mv --force '$from' '$to'");
}

