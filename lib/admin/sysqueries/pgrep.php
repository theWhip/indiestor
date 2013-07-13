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
	check if a particular process is running already
	returns a list of pid numbers (process numbers)
*/

function sysquery_pgrep($pattern)
{
	$pids=array();
	$query="pgrep --full '$pattern'";
	$result=ShellCommand::query($query,true);
	if($result->returnCode!=0) return $pids;
	$lines=explode("\n",$result->stdout);
	foreach($lines as $line)
		if(trim($line)!='')
			$pids[]=intval($line);
	return $pids;
}

