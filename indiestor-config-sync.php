#!/usr/bin/php
<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('lib/etcfiles/EtcGroup.php');
require_once('lib/JSONFile.php');
require_once('lib/GroupSync.php');
require_once('indiestor-cl-args.php');

$etcGroup=new EtcGroup();
$indiestorGroups=JSONFile::load($groupsFilePath);
if(!file_exists($previousGroupsFilePath))
{
	file_put_contents($previousGroupsFilePath,'[]');
}
$indiestorPreviousGroups=JSONFile::load($previousGroupsFilePath);

$groupSync=new GroupSync($etcGroup,$indiestorGroups,$indiestorPreviousGroups);
$groupSync->process();
//make sure the indiestor group always exists
$groupSync->repairGroup('indiestor');
//only do this if processing was successful
unlink($previousGroupsFilePath);
copy($groupsFilePath,$previousGroupsFilePath);

