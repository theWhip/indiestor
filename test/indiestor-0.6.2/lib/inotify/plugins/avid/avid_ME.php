<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/
function avid_ME($repeater)
{
        $FSPaths=new FSPaths();

	if(!someoneHasAnAvidProject($repeater)) 
        {
                return $FSPaths;
        }

        $FSPaths->addPaths(avid_ME_MXFFolders($repeater));
        return $FSPaths;
}

function avid_ME_MXFFolders($repeater)
{
	$FSPaths=new FSPaths();
        
        for($i=1;$i<=avidMXFCountForMember($repeater);$i++)
        {
        	$FSPaths->add(avid_ME_MXFFolderForMXFNumber($repeater,$i));
        }

	return $FSPaths;
}

function avid_ME_MXFFolderForMXFNumber($repeater,$MXFNumber)
{
        $groupName=$repeater->group->name;
        $memberName=$repeater->member->name;

	$FSPath=new FSPath();	
	$FSPath->type=__FUNCTION__;
	$FSPath->path="/home/$memberName/Avid MediaFiles/MXF/$MXFNumber/";
	$FSPath->owner=$memberName;
	$FSPath->group=$groupName;
	$FSPath->permissions='drwxr-x---';
        return $FSPath;
}

