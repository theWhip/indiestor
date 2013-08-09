<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

function require_once_folder($folderPath)
{
	$folder=dir($folderPath);
	while (false !== ($entry = $folder->read()))
	{
		if($entry=='.') continue;
		if($entry=='..') continue;
		if(is_dir($folderPath.'/'.$entry)) continue;
		if(!is_file($folderPath.'/'.$entry)) continue;
		if(strlen($entry)<strlen('.php')) continue;
		if(substr($entry,-strlen('.php'))!=='.php') continue;
		require_once($folderPath.'/'.$entry);
	}
	$folder->close();
}

