#!/usr/bin/php
<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('lib/JSONFile.php');
require_once('lib/repairengine/RepairEngine.php');
require_once('indiestor-cl-args.php');

//if needed, create a empty backup config
if(!file_exists($previousGroupsFilePath))
{
	file_put_contents($previousGroupsFilePath,'[]');
}

//load config and previous config
$indiestorPreviousGroups=JSONFile::load($previousGroupsFilePath);
$indiestorGroups=JSONFile::load($groupsFilePath);

//repair
RepairEngine::repair($indiestorGroups,$indiestorPreviousGroups);

//only do this if processing was successful
unlink($previousGroupsFilePath);
copy($groupsFilePath,$previousGroupsFilePath);

