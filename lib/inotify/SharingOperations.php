<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

requireLibFile('inotify/syslog.php');
requireLibFile('inotify/shellSilent.php');

class SharingOperations
{

	static function verifySymlink($linkName,$target,$owner)
	{
		if(is_link($linkName))
		{
			$currentTarget=readlink($linkName);
			if($currentTarget!=$target)
			{
				if(!file_exists($target))
					mkdir($target,0755,true);
				syslog_notice("moving content of '$currentTarget' to '$target'.");
				shell_exec("mv -f '$currentTarget'/* '$target'");
				unlink($linkName);
				self::createSymLink($linkName,$target,$owner);
			}
			else
			{
				self::ensureLinkOwnership($linkName,$owner);
			}
		}
		else
		{
			//delete the linkname if it is a file or a folder
			if(is_file($linkName)) unlink($linkName);
			else if(is_dir($linkName)) rmdir($linkName);

			self::createSymLink($linkName,$target,$owner);
		}
	}

	static function createSymlink($linkName,$target,$userName)
	{
		syslog_notice("symlinking link:'$linkName',target:'$target'");
		$result=symlink($target,$linkName);
		if($result==true) syslog_notice('symlink successfully created');
		else  syslog_notice('error creating symlink');
		self::ensureLinkOwnership($linkName,$userName);
	}

	static function ensureLinkOwnership($linkName,$userName)
	{
		$lstat=lstat($linkName);
		if($lstat==null)
		{
			syslog_notice("Cannot lstat symlink '$linkName'");
			return;
		}

		$owner=self::ownerByUid($lstat['uid']);
		$group=self::groupByGid($lstat['gid']);

		if($owner!=$userName || $group!=$userName) 
		{
			shellSilent("chown --no-dereference $userName.$userName '$linkName'");
			syslog_notice("changed ownership of '$linkName' to '$userName.$userName'");
		}
	}

	static function ownerByUid($uid)
	{
		$ownerArray=posix_getpwuid($uid);
		$owner=$ownerArray['name'];
		return $owner;
	}

	static function groupByGid($gid)
	{
		$groupArray=posix_getgrgid($gid);
		$group=$groupArray['name'];
		return $group;
	}

	static function fixOwnerGroup($groupName,$userName,$fsObject)
	{
		$stat=stat($fsObject);
		if($stat==null)
		{
			syslog_notice("Cannot stat '$fsObject'");
			return;
		}
		$currentOwner=SharingOperations::ownerByUid($stat['uid']);	
		if($currentOwner!=$userName) 
		{
			$result=chown($fsObject,$userName);
			if(!$result)
				syslog_notice("cannot chown '$fsObject' to '$userName'");
		}

		$currentGroup=SharingOperations::groupByGid($stat['gid']);
		if(!empty($groupName) && !empty($currentGroup) && $currentGroup!=$groupName)
		{
			$result=chgrp($fsObject,$groupName);
			if(!$result)
				syslog_notice("cannot chgrp '$fsObject' to '$groupName'");
		}
	}

	static function fixProjectFsObjectOwnership($groupName,$userName,$fsObject)
	{
		$stat=stat($fsObject);
		if($stat==null)
		{
			syslog_notice("Cannot stat '$fsObject'");
			return;
		}

		//owner must be user

		$currentOwner=SharingOperations::ownerByUid($stat['uid']);	
		if($currentOwner!=$userName) 
		{
			$result=chown($fsObject,$userName);
			if(!$result)
				syslog_notice("cannot chown '$fsObject' to '$userName'");
		}

		//group must be the indiestor group

		$currentGroup=SharingOperations::groupByGid($stat['gid']);
		if(!empty($groupName) && !empty($currentGroup) && $currentGroup!='is_'.$groupName)
		{
			$result=chgrp($fsObject,'is_'.$groupName);
			if(!$result)
				syslog_notice("cannot chgrp '$fsObject' to 'is_$groupName'");
		}
	}

	static function fixUserObjectOwnership($userName,$fsObject)
	{
		$stat=stat($fsObject);
		if($stat==null)
		{
			syslog_notice("Cannot stat '$fsObject'");
			return;
		}

		//owner must be user

		$currentOwner=SharingOperations::ownerByUid($stat['uid']);	
		if($currentOwner!=$userName) 
		{
			$result=chown($fsObject,$userName);
			if(!$result)
				syslog_notice("cannot chown '$fsObject' to '$userName'");
		}

		//group is also owner

		$currentGroup=SharingOperations::groupByGid($stat['gid']);
		if($currentGroup!=$userName)
		{
			$result=chgrp($fsObject,$userName);
			if(!$result)
				syslog_notice("cannot chgrp '$fsObject' to '$userName'");
		}
	}

	static function fixFsObjectPermissions($fsObject,$mode)
	{
		$stat=stat($fsObject);

		if($stat==null)
		{
			syslog_notice("Cannot stat '$fsObject'");
			return;
		}

		$currentMode=substr(decoct($stat['mode']),-strlen($mode));

		if($currentMode!=$mode)
		{
			$result=chmod($fsObject,octdec($mode));
			if(!$result)
				syslog_notice("cannot chmod '$fsObject' to '$mode'");

		}
	}

	static function fixProjectFilePermissions($file)
	{
		//permissions must be owner=rwx group=rwx other=---
		self::fixFsObjectPermissions($file,"660");
	}

	static function fixProjectFolderPermissions($folder)
	{
		//permissions must be owner=rwx group=rwx other=---
		//sticky bit must be set: only the owner of a project file/folder may delete it
		//setgid must be set: all files/folders created must inherit the group id
		//Other must have execute rights for sticky bit to work

		if(SharingFolders::endsWith($folder,'.avid'))
			self::fixFsObjectPermissions($folder,"2771");
		if(SharingFolders::endsWith($folder,'.shared'))
			self::fixFsObjectPermissions($folder,"750");
	}

	static function renameProjectFileIfNeeded($userName,$oldName,$newName)
	{
		if($oldName!=$newName)
		{
			$result=rename($oldName,$newName);
			if(!$result)
			{
				syslog_notice("Failed to rename '$oldName' to '$newName'"); 
				return;
			}
		}

		self::fixUserObjectOwnership($userName,$newName);
		self::fixProjectFilePermissions($newName);

	}

	static function renameAvpProjectFile($userName,$homeFolder,$project,$file)
	{
		$oldName="$homeFolder/$project/$file";
		$newName="$homeFolder/$project/$project.avp";
		self::renameProjectFileIfNeeded($userName,$oldName,$newName);
	}

	static function renameAvsProjectFile($userName,$homeFolder,$project,$file)
	{
		$oldName="$homeFolder/$project/$file";

		/*
		/home/user/hello/hello.avid.avs
		/home/user/hello/hello.avid Settings.avs
		With max. lenght of hello.avid maximum 18 chars.
		With max. total length 27 chars.
		*/

		$prefix=substr($project,0,18);
		$avsFile="$prefix Settings.avs";
		$newName="$homeFolder/$project/$avsFile";

		self::renameProjectFileIfNeeded($userName,$oldName,$newName);
	}

	static function purgeProjectLink($projectLinkPath,$users)
	{
		$target=readlink($projectLinkPath);

		//if the link does not point to a folder, remove it
		if(!is_dir($target))
		{
			unlink($projectLinkPath);
			syslog_notice("PurgeProjectLink: Removed '$projectLinkPath'; ".
                                "target '$target' is not a valid link target");
			return;
		}

		//the link must point to a project folder
		$targetProjectFolder=basename($target);
		if(!SharingFolders::isProjectFolder($targetProjectFolder))
		{
			unlink($projectLinkPath);
			syslog_notice("Removed '$projectLinkPath'; in target '$target' ".
				"the target '$targetProjectFolder' is not a valid project folder");
			return;
		}
		//the link must point to member project folder
		$targetHomeFolder=dirname($target);
		if(!SharingFolders::isGroupMemberHomeFolder($users,$targetHomeFolder))
		{
			unlink($projectLinkPath);
			syslog_notice("Removed '$projectLinkPath'; in target '$target' ".
				"the home folder '$targetHomeFolder' is not the home folder for a group member");
			return;
		}
	}
}

