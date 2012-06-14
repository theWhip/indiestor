<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/
function default_ME($repeater)
{
	$FSPaths=new FSPaths();

        $groupName=$repeater->group->name;
        $memberName=$repeater->member->name;

	$FSPath=new FSPath();	
	$FSPath->type=__FUNCTION__;
	$FSPath->path="/home/$memberName/";
	$FSPath->owner=$memberName;
	$FSPath->group=$groupName;
	$FSPath->permissions='drwx------';
	$FSPaths->add($FSPath);

        return $FSPaths;
}

