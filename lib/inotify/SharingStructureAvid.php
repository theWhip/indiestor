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

class SharingStructureAvid
{

	static function reshare($groupName,$users)
	{
		if($users==null) $users=array();
		syslog_notice("resharing group '$groupName'for avid folders");
		self::verifyProjects($groupName,$users);
#		self::purgeProjectLinks($users);
	}

	static function verifyProjects($groupName,$users)
	{
		foreach($users as $user)
		{
			$projects=sharingFolders::userAvidProjects($user->homeFolder);
			foreach($projects as $project)
			{
				self::verifyProject($groupName,$user,$project,$users);
				self::verifyProjectSharing($groupName,$user,$project,$users);
			}
		}
	}


	static function verifyProject($groupName,$user,$project,$users)
	{
		self::verifyProjectFiles($groupName,$user,$project);
		self::verifyProjectSharedFolder($groupName,$user,$project,$users);
	}

	static function verifyProjectSharedFolder($groupName,$user,$project,$users)
	{
		$userName=$user->name;
		$homeFolder=$user->homeFolder;
		$projectFolder=$homeFolder."/".$project;
		$shared="$projectFolder/Shared";
		if(!is_dir($shared)) mkdir($shared);
		SharingOperations::fixFsObjectPermissions($shared,"755");

		#the owner's own shared subfolder
		$sharedSubOwner="$shared/$userName";
		if(!is_dir($sharedSubOwner)) mkdir($sharedSubOwner);
		SharingOperations::fixProjectFsObjectOwnership($groupName,$userName,$sharedSubOwner);
		SharingOperations::fixFsObjectPermissions($sharedSubOwner,"755");

		#avid copy 
		$projectCopy=self::folderAvidToCopy($project);

		#link for each other member
		foreach($users as $sharingUser)
		{
			if($sharingUser->name!=$userName)
			{
				$linkName="$shared/{$sharingUser->name}";
				$target="{$sharingUser->homeFolder}/Avid Shared Projects/$projectCopy/Shared/{$sharingUser->name}";
				SharingOperations::verifySymLink($linkName,$target,$userName);		
			}
		}				
	}

	static function verifyProjectFiles($groupName,$user,$project)
	{
		$userName=$user->name;
		$homeFolder=$user->homeFolder;
		if ($handle = opendir("$homeFolder/$project"))
		{
			while(false !== ($entry = readdir($handle)))
			{
				if(is_file("$homeFolder/$project/$entry"))
				{
					if(SharingFolders::endsWith($entry,'.avp'))
						SharingOperations::renameAvpProjectFile($userName,$homeFolder,$project,$entry);
					if(SharingFolders::endsWith($entry,'.avs'))
						SharingOperations::renameAvsProjectFile($userName,$homeFolder,$project,$entry);
				}
			}
			closedir($handle);
		}
		else
		{
			syslog_notice("Cannot open folder '$homeFolder/$project' for renaming .avp and .avs files");
		}
	}

	static function verifyProjectSharing($groupName,$owner,$project,$users)
	{
		foreach($users as $user)
		{
			if($user->name!=$owner->name)
			{
				self::verifyProjectSharingMember($groupName,$owner,$user,$project,$users);
			}
		}
	}

	static function folderAvidToCopy($folderName)
	{
		$prefix=substr($folderName,0,strlen($folderName)-strlen('.avid'));
		return "$prefix.copy";
	}

	static function verifyProjectSharingMember($groupName,$owner,$user,$project,$users)
	{
		#the user's Avid Shared Projects folder
		$aspFolder="{$user->homeFolder}/Avid Shared Projects";
		if(!is_dir($aspFolder)) mkdir($aspFolder);
		SharingOperations::fixProjectFsObjectOwnership($groupName,$user->name,$aspFolder);
		SharingOperations::fixFsObjectPermissions($aspFolder,"755");

		#the user's project.copy folder
		$projectCopy=self::folderAvidToCopy($project);
		$prjCopyFolder="$aspFolder/$projectCopy";
		if(!is_dir($prjCopyFolder)) mkdir($prjCopyFolder);
		SharingOperations::fixProjectFsObjectOwnership($groupName,$user->name,$prjCopyFolder);
		SharingOperations::fixFsObjectPermissions($prjCopyFolder,"755");

		#copy avp and avs files
		self::copyAvidProjectFiles("{$owner->homeFolder}/$project",$prjCopyFolder,$user->name);

		#the user's shared folder
		$shared="$prjCopyFolder/Shared";
		if(!is_dir($shared)) mkdir($shared);
		SharingOperations::fixProjectFsObjectOwnership($groupName,$user->name,$shared);
		SharingOperations::fixFsObjectPermissions($shared,"755");

		#the user's own shared subfolder
		$sharedSubUser="$shared/{$user->name}";
		if(!is_dir($sharedSubUser)) mkdir($sharedSubUser);
		SharingOperations::fixProjectFsObjectOwnership($groupName,$user->name,$sharedSubUser);
		SharingOperations::fixFsObjectPermissions($sharedSubUser,"755");

		#the link from the project owner
		$sharedSubOwner="$shared/{$owner->name}";
		$target="{$owner->homeFolder}/$project/Shared/{$owner->name}";
		SharingOperations::verifySymLink($sharedSubOwner,$target,$user->name);		

		#all other users (not the member himself, nor the owner)
		foreach($users as $sharingMember)
		{
			if($sharingMember->name!=$owner->name && $sharingMember->name!=$user->name)
			{
				$linkName="$shared/{$sharingMember->name}";
				$target="{$sharingMember->homeFolder}/Avid Shared Projects/$projectCopy/Shared/{$sharingMember->name}";
				SharingOperations::verifySymLink($linkName,$target,$user->name);		
			}
		}		
	}

	static function copyAvidProjectFiles($ownerProjectFolder,$sharingMemberCopyFolder,$memberName)
	{
		if ($handle = opendir($ownerProjectFolder))
		{
			while(false !== ($entry = readdir($handle)))
			{
				$source="$ownerProjectFolder/$entry";
				if(is_file($source))
				{
					if(SharingFolders::endsWith($entry,'.avp') || SharingFolders::endsWith($entry,'.avs'))
					{
						$copy=str_replace('.avid','.copy',$entry);
						$target="$sharingMemberCopyFolder/$copy";
						if(!file_exists($target)) copy($source,$target);
						SharingOperations::fixUserObjectOwnership($memberName,$target);
						SharingOperations::fixFsObjectPermissions($target,"750");
					}
				}
			}
			closedir($handle);
		}
		else
		{
			syslog_notice("Cannot open folder '$ownerProjectFolder' for copying .avp and .avs files");
		}
	}

	static function purgeProjectLinks($users)
	{
/*
		foreach($users as $user)
		{
			$projectLinks=SharingFolders::userProjectLinks($user->homeFolder);
			foreach($projectLinks as $projectLink)
				SharingOperations::purgeProjectLink($user->name,$user->homeFolder,$projectLink,$users);
		}
*/
	}
}

