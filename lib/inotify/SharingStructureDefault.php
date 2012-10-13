<?php
/*
        Indiestor inotify program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once(dirname(__FILE__).'/syslog.php');
require_once(dirname(__FILE__).'/SharingOperations.php');
require_once(dirname(__FILE__).'/SharingFolders.php');

class SharingStructureDefault
{

	static function reshare($groupName,$users)
	{
		if($users==null) $users=array();
		syslog_notice("resharing group '$groupName' for default projects");
		self::verifyProjectLinks($groupName,$users);
		self::purgeProjectLinks($users);
	}

	static function verifyProjectLinks($groupName,$users)
	{
		if($users==null) $users=array();

		foreach($users as $user)
		{
			$projects=sharingFolders::userDefaultProjects($user->homeFolder);
			foreach($projects as $project)
			{
				$projectFolder=$user->homeFolder."/".$project;
				SharingOperations::fixProjectFsObjectOwnership($groupName,$user->name,$projectFolder);
				SharingOperations::fixProjectFolderPermissions($projectFolder);
				foreach($users as $sharingUser)
					if($user->name!=$sharingUser->name)
						self::verifyProjectLink($user,$sharingUser,$project);
			}
		}
	}

	static function verifyProjectLink($user,$sharingUser,$project)
	{
		$linkName="{$sharingUser->homeFolder}/$project";
		$target="{$user->homeFolder}/$project";
		SharingOperations::verifySymLink($linkName,$target,$sharingUser->name);		
	}

	static function purgeProjectLinks($users)
	{
		foreach($users as $user)
		{
			$projectLinks=SharingFolders::userProjectLinks($user->homeFolder);
			foreach($projectLinks as $projectLink)
				SharingOperations::purgeProjectLink("{$user->homeFolder}/$projectLink",$users);
		}
	}
}

