<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/
function default_ME_FO_SM($repeater)
{
	$FSPaths=new FSPaths();

        $groupName=$repeater->group->name;
        $memberNameFrom=$repeater->member->name;
        $memberNameTo=$repeater->sharingMember->name;
        $folder=$repeater->folder;

	$FSPath=new FSPath();	
	$FSPath->type=__FUNCTION__;
	$FSPath->path="/home/{$memberNameTo}/$folder.shared/";
	$FSPath->linkedToPath="/home/{$memberNameFrom}/$folder.shared/";
	$FSPath->owner=$memberNameTo;
	$FSPath->group=$memberNameTo;
	$FSPath->permissions='lrwxrwx---';

	$FSPaths->add($FSPath);
        return $FSPaths;
}

