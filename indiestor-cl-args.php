<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

if($argc==1) 
{
	//no commandline parameter supplied; revert to default filename
	$groupsFilePath=dirname(__FILE__).'/groups.json';
	$previousGroupsFilePath=dirname(__FILE__).'/groups.previous.json';
	$memberFoldersFilePath=dirname(__FILE__).'/member-folders.json';
}
else if($argc==3)
{
	$groupsFilePath=$argv[1];
	$memberFoldersFilePath=$argv[2];
}
else
{
        die("supply a groups.json and a member-folders.json file or else no files\n");
}

