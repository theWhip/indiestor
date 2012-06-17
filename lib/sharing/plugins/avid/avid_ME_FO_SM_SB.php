<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

function avid_ME_FO_SM_SB($repeater)
{
	$memberName=$repeater->member->name;
	$sharingMemberName=$repeater->sharingMember->name;
	$shareBackMemberName=$repeater->shareBackMember->name;
	$folder=$repeater->folder;

	$FSPaths=new FSPaths();

        $FSPath=new FSPath();	
        $FSPath->type=__FUNCTION__;
        $FSPath->path="/home/{$sharingMemberName}/$folder".avidProjectFolderSuffix($sharingMemberName,$repeater)."/{$shareBackMemberName}.bin/";
        $FSPath->linkedToPath="/home/$shareBackMemberName/$folder".avidProjectFolderSuffix($shareBackMemberName,$repeater)."/{$shareBackMemberName}.bin/";
        $FSPath->owner=$sharingMemberName;
        $FSPath->group=$sharingMemberName;
        $FSPath->permissions='lrwxrwx---';
	$FSPaths->add($FSPath);

        return $FSPaths;
}

