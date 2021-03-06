<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

//--------------------------
// CHMOD RECURSIVE
//--------------------------

function chmodBase($path,$mode,$userName,$groupName)
{

        //check permission
	if(!file_exists($path)) return; //this could happen
	$currentMode=fileperms($path) & 0777;
	if($currentMode!=$mode)
	{
		if(!file_exists($path)) return; //this could happen
	        chmod($path, $mode);
	        $currentModeOct=decoct($currentMode);
	        $modeOct=decoct($mode);
	        syslog_notice("chmodBase-permissions: $path: $currentModeOct => $modeOct");
	}

        //check ownership
	if(!file_exists($path)) return; //this could happen
        $userRecord=posix_getpwuid(fileowner($path));
        $currentOwner=$userRecord['name'];
        if($currentOwner!=$userName)
        {
                chown($path,$userName);
                syslog_notice("chmodBase-owner: $path: $currentOwner => $userName");
        }
	if(!file_exists($path)) return; //this could happen
        $groupRecord=posix_getgrgid(filegroup($path));
        $currentGroup=$groupRecord['name'];
        if($currentGroup!=$groupName)
        {
		if(!file_exists($path)) return; //this could happen
                chgrp($path,$groupName);
                syslog_notice("chmodBase-group: $path: $currentGroup => $groupName");
        }
}

function chmodRecursive($path, $modeFile,$modeFolder,$userName,$groupName,$clearStatCache=true) 
{ 
	if($clearStatCache) clearstatcache();
        if (!is_link($path))
        {
                if (is_dir($path)) 
                {
                        chmodBase($path,$modeFolder,$userName,$groupName);
                        $dh = opendir($path); 
                        while (($file = readdir($dh)) !== false) 
                                if($file != '.' && $file != '..')
                                        chmodRecursive($path.'/'.$file, $modeFile,
						$modeFolder,$userName,$groupName,false); 
                        closedir($dh); 
                } 
                else chmodBase($path,$modeFile,$userName,$groupName);
        }
} 

