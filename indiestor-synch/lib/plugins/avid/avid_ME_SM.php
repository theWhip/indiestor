<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

function avid_ME_SM($repeater)
{
	$FSPaths=new FSPaths();

	if(!someoneHasAnAvidProject($repeater)) 
        {
                return $FSPaths;
        }

        for($i=1;$i<=avidMXFCountForMember($repeater);$i++)
        {
        	$FSPaths->add(avid_ME_SM_ForMXFNumber($repeater,$i));
        }

        return $FSPaths;
}

function avid_ME_SM_ForMXFNumber($repeater,$MXFNumber)
{
        $groupName=$repeater->group->name;
        $memberNameFrom=$repeater->member->name;
	$memberNameTo=$repeater->sharingMember->name;

	$FSPath=new FSPath();	
	$FSPath->type=__FUNCTION__;
	$FSPath->path="/home/{$memberNameTo}/Avid MediaFiles/MXF/${MXFNumber}_$memberNameFrom/";
	$FSPath->linkedToPath="/home/{$memberNameFrom}/Avid MediaFiles/MXF/$MXFNumber/";
	$FSPath->owner=$memberNameTo;
	$FSPath->group=$memberNameTo;
	$FSPath->permissions='lrwxrwx---';
        
        return $FSPath;
}

