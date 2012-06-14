<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/
function default_ME_FO($repeater)
{
	$FSPaths=new FSPaths();

        $groupName=$repeater->group->name;
        $memberName=$repeater->member->name;
        $folder=$repeater->folder;

	$FSPath=new FSPath();	
	$FSPath->type=__FUNCTION__;
	$FSPath->owner=$memberName;
	$FSPath->path="/home/$memberName/$folder.shared/";
	$FSPath->group=$groupName;
	$FSPath->permissions='drwxr-x---';
	$FSPaths->add($FSPath);
        return $FSPaths;
}

