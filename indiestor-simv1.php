#!/usr/bin/php
<?php

require_once('lib/GroupDefinitions.php');
if($argc==1) 
{
	//no commandline parameter supplied; revert to default filename
	$groupsFilePath=dirname(__FILE__).'/groups.json';
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
$groupDefinitions=new GroupDefinitions();
$groupDefinitions->loadFiles($groupsFilePath,$memberFoldersFilePath);
$groupDefinitions->process();
$groupDefinitions->FSPaths->dump();

