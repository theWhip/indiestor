<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/
function someoneHasAnAvidProject($repeater)
{
	foreach($repeater->members as $member)
	{
		if(array_key_exists('folders',$member)) 
		        if($member->folders!=null) 
                        {
		                if(array_key_exists('avid',$member->folders)) 
		                {
			                if(count($member->folders->avid)>0) return true;
		                }
                        }
	}
	return false;
}

function avidMXFCountForMember($repeater)
{
        if(array_key_exists('mxfcount',$repeater->member))
        {
                return $repeater->member->mxfcount;
        }
        else
        {
                return 1;
        }
}

function avidProjectFolderSuffix($memberName,$repeater)
{
	if($memberName==$repeater->member->name) return ".avid.shared";
	else return ".avid.copy";
}

