<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/
function avid_ME_FO_SM($repeater)
{
	$FSPaths=new FSPaths();

        $memberNameFrom=$repeater->member->name;
        $memberNameTo=$repeater->sharingMember->name;
        $folder=$repeater->folder;

	$FSPath=new FSPath();	
	$FSPath->type=__FUNCTION__;
	$FSPath->path="/home/{$memberNameTo}/$folder".
		avidProjectFolderSuffix($memberNameTo,$repeater)."/";
	$FSPath->linkedToPath="";
	$FSPath->owner=$memberNameTo;
	$FSPath->group=$memberNameTo;
	$FSPath->permissions='drwxr-x---';

	$FSPaths->add($FSPath);
        $FSPaths->addPaths(avid_ME_FO_SM_FI($repeater,$FSPath));
        $FSPaths->addPaths(avid_ME_FO_SM_ShareBack($repeater));
        $FSPaths->addPaths(avid_ME_FO_SM_ShareBack_Self($repeater));

        return $FSPaths;
}

function avid_ME_FO_SM_FI($repeater,$FSPath)
{
        $memberNameFrom=$repeater->member->name;
        $memberNameTo=$repeater->sharingMember->name;
        $folder=$repeater->folder;

	$FSPaths=new FSPaths();	

        $filePath=$FSPath->copy();
	$filePath->permissions='-rwxr-w---';
	$filePath->type=__FUNCTION__;

	$fullFolderName=$folder.avidProjectFolderSuffix($memberNameTo,$repeater);

        //AVP FILE
        $filePath=$filePath->copy();
        $filePath->path="/home/$memberNameTo/$fullFolderName/$fullFolderName.avp";
	$filePath->linkedToPath="";
	$FSPaths->add($filePath);

        //AVS FILE
        $filePath=$filePath->copy();
        $filePath->path="/home/$memberNameTo/$fullFolderName/$fullFolderName.avs";
	$filePath->linkedToPath="";
	$FSPaths->add($filePath);

        return $FSPaths;
}

function avid_ME_FO_SM_ShareBack($repeater)
{
        $groupName=$repeater->group->name;
        $memberName=$repeater->member->name;
	$sharingMemberName=$repeater->sharingMember->name;
        $folder=$repeater->folder;

	$FSPaths=new FSPaths();	

        $FSPath=new FSPath();
        $FSPath->type=__FUNCTION__;
        $FSPath->owner=$memberName;
        $FSPath->path="/home/$memberName/$folder".avidProjectFolderSuffix($memberName,$repeater)."/$sharingMemberName.bin/";
	$FSPath->linkedToPath="/home/{$sharingMemberName}/$folder".avidProjectFolderSuffix($sharingMemberName,$repeater)."/{$sharingMemberName}.bin/";
        $FSPath->permissions='lrwxrwx---';
        $FSPath->group=$memberName;
        $FSPaths->add($FSPath);                                        

        return $FSPaths;
}

function avid_ME_FO_SM_ShareBack_Self($repeater)
{
	$FSPaths=new FSPaths();	

        $groupName=$repeater->group->name;
        $memberNameFrom=$repeater->member->name;
        $memberNameTo=$repeater->sharingMember->name;
        $folder=$repeater->folder;

        $FSPath=new FSPath();	
        $FSPath->type=__FUNCTION__;
        $FSPath->path="/home/$memberNameTo/$folder".avidProjectFolderSuffix($memberNameTo,$repeater)."/$memberNameTo.bin/";
        $FSPath->linkedToPath="";
        $FSPath->owner=$memberNameTo;
        $FSPath->group=$groupName;
        $FSPath->permissions='drwxr-x---';
	$FSPaths->add($FSPath);

        return $FSPaths;
}


