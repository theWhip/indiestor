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

# zpool list
NAME                    SIZE    ALLOC   FREE    CAP  HEALTH     ALTROOT
tank                   80.0G   22.3G   47.7G    28%  ONLINE     -
dozer                   1.2T    384G    816G    32%  ONLINE     -

We only need the name column (at this point):

tank
dozer

*/

function sysquery_zpool_list()
{
	$zpools=array();
	$result=ShellCommand::query("zpool list | tail -n +2 | awk '{ print $1 }",true);
	if($result->returnCode!=0) return $zpools;
	$lines=explode("\n",$result->stdout);
	foreach($lines as $line)
		if(trim($line)!='')
			$zpools[$line]=$line;
	return $zpools;
}

