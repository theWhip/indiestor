<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/
function avid_ME_FO($repeater)
{
        $groupName=$repeater->group->name;
        $memberName=$repeater->member->name;
        $folder=$repeater->folder;

	$FSPaths=new FSPaths();	
	$FSPath=new FSPath();	
	$FSPath->type=__FUNCTION__;
	$FSPath->owner=$memberName;
	$FSPath->path="/home/$memberName/$folder".
		avidProjectFolderSuffix($memberName,$repeater)."/";
	$FSPath->group=$groupName;
	$FSPath->permissions='drwxr-x---';
	$FSPaths->add($FSPath);
        $FSPaths->addPaths(avid_ME_FO_FI($repeater,$FSPath));
        $FSPaths->addPaths(avid_ME_FO_ShareBack($repeater));
        return $FSPaths;
}

function avid_ME_FO_FI($repeater,$FSPath)
{
        $groupName=$repeater->group->name;
        $memberName=$repeater->member->name;
        $folder=$repeater->folder;

	$FSPaths=new FSPaths();	

        $filePath=$FSPath->copy();
	$filePath->permissions='-rwxr-x---';
	$filePath->type=__FUNCTION__;

	//avid folder name
	$fullFolderName=$folder.avidProjectFolderSuffix($memberName,$repeater);

	//AVP FILE
        $filePath=$filePath->copy();
	$filePath->path="/home/$memberName/$fullFolderName/$fullFolderName.avp";
	$FSPaths->add($filePath);

	//AVS FILE
        $filePath=$filePath->copy();
	$filePath->path="/home/$memberName/$fullFolderName/$fullFolderName.avs";
	$FSPaths->add($filePath);

        return $FSPaths;
}

function avid_ME_FO_ShareBack($repeater)
{
        $groupName=$repeater->group->name;
        $memberName=$repeater->member->name;
        $folder=$repeater->folder;

	$FSPaths=new FSPaths();	

        $FSPath=new FSPath();
        $FSPath->type=__FUNCTION__;
        $FSPath->owner=$memberName;
        $FSPath->path="/home/$memberName/$folder".
		avidProjectFolderSuffix($memberName,$repeater)."/$memberName.bin/";
	$FSPath->linkedToPath="";
        $FSPath->permissions='drwxr-x---';
        $FSPath->group=$groupName;
        $FSPaths->add($FSPath);                                        

        return $FSPaths;
}

