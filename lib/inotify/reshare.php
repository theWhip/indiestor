<?php

/*
        Indiestor inotify program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

function reshare($groupName,$users)
{
	verifyProjectLinks($groupName,$users);
	purgeProjectLinks($users);
}

function verifyProjectLinks($groupName,$users)
{
	$avidProjectsPresent=false;
	foreach($users as $user)
	{
		$projects=userProjects($user->homeFolder);
		$avidProjectsPresent=$avidProjectsPresent || hasAtLeastOneAvidProject($projects);
		foreach($projects as $project)
		{
			verifyProjectFiles($groupName,$user,$project);
			$projectFolder=$user->homeFolder."/".$project;
			fixProjectFsObjectOwnership($groupName,$user->name,$projectFolder);
			fixProjectFolderPermissions($projectFolder);
			foreach($users as $sharingUser)
				if($user->name!=$sharingUser->name)
					verifyProjectLink($user,$sharingUser,$project);
		}
	}
	if($avidProjectsPresent)
		reshareAvid($users);

	purgeAvid($users);
}

function hasAtLeastOneAvidProject($projects)
{
	foreach($projects as $project)
	{
		if(endsWith($project,'.avid')) return true;
	}
	return false;
}

function userProjects($homeFolder)
{
	return userProjectsOrLinks($homeFolder,'project');
}

function userProjectLinks($homeFolder)
{
	return userProjectsOrLinks($homeFolder,'link');
}

function isRejectedFolderEntry($entry)
{
	if($entry=='.') return true;
	if($entry=='..') return true;
	return false;
}

function isRequiredFolderType($folder,$type)
{
	if($type=='project' && !is_dir($folder)) return false;
	if($type=='project' && is_link($folder)) return false;
	if($type=='link' && !is_link($folder)) return false;
	return true;
}

function userProjectsOrLinks($homeFolder,$type)
{
	$projects=array();
	if ($handle = opendir($homeFolder))
	{
		while(false !== ($entry = readdir($handle)))
		{
			if(
				!isRejectedFolderEntry($entry)  && 
				isProjectFolder($entry) &&
				isRequiredFolderType("$homeFolder/$entry",$type)
			)
				$projects[$entry]=$entry;
		}
		closedir($handle);
	}
	return $projects;
}

function fixProjectFsObjectOwnership($groupName,$userName,$fsObject)
{
	$stat=stat($fsObject);
	if($stat==null)
	{
		syslog_notice("Cannot stat '$fsObject'");
		return;
	}

	//owner must be user

	$currentOwner=ownerByUid($stat['uid']);	
	if($currentOwner!=$userName) 
	{
		$result=chown($fsObject,$userName);
		if(!$result)
			syslog_notice("cannot chown '$fsObject' to '$userName'");
	}

	//group must be the indiestor group

	$currentGroup=groupByGid($stat['gid']);
	if($currentGroup!='is_'.$groupName)
	{
		$result=chgrp($fsObject,'is_'.$groupName);
		if(!$result)
			syslog_notice("cannot chgrp '$fsObject' to 'is_$groupName'");
	}

}

function fixFsObjectPermissions($fsObject,$mode)
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

function fixProjectFilePermissions($file)
{
	//permissions must be owner=rwx group=rwx other=---
	fixFsObjectPermissions($file,"770");
}

function fixProjectFolderPermissions($folder)
{
	//permissions must be owner=rwx group=rwx other=---
	//sticky bit must be set: only the owner of a project file/folder may delete it
	//setgid must be set: all files/folders created must inherit the group id
	//Other must have execute rights for sticky bit to work

	fixFsObjectPermissions($folder,"3771");
}

function renameProjectFileIfNeeded($groupName,$userName,$oldName,$newName)
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

	fixProjectFsObjectOwnership($groupName,$userName,$newName);
	fixProjectFilePermissions($newName);

}

function renameAvpProjectFile($groupName,$userName,$homeFolder,$project,$file)
{
	$oldName="$homeFolder/$project/$file";
	$newName="$homeFolder/$project/$project.avp";
	renameProjectFileIfNeeded($groupName,$userName,$oldName,$newName);
}

function renameAvsProjectFile($groupName,$userName,$homeFolder,$project,$file)
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

	renameProjectFileIfNeeded($groupName,$userName,$oldName,$newName);
}


function verifyProjectFiles($groupName,$user,$project)
{
	$userName=$user->name;
	$homeFolder=$user->homeFolder;
	if ($handle = opendir("$homeFolder/$project"))
	{
		while(false !== ($entry = readdir($handle)))
		{
			if(is_file("$homeFolder/$project/$entry"))
			{
				if(endsWith($entry,'.avp'))
					renameAvpProjectFile($groupName,$userName,$homeFolder,$project,$entry);
				if(endsWith($entry,'.avs'))
					renameAvsProjectFile($groupName,$userName,$homeFolder,$project,$entry);
			}
		}
		closedir($handle);
	}
	else
	{
		syslog_notice("Cannot open folder '$homeFolder/$project' for renaming .avp and .avs files");
	}
}

function ownerByUid($uid)
{
	$ownerArray=posix_getpwuid($uid);
	$owner=$ownerArray['name'];
	return $owner;
}

function groupByGid($gid)
{
	$groupArray=posix_getgrgid($gid);
	$group=$groupArray['name'];
	return $group;
}

function createSymlink($linkName,$target,$userName)
{
	syslog_notice("symlinking link:'$linkName',target:'$target'");
	$result=symlink($target,$linkName);
	if($result==true) syslog_notice('symlink successfully created');
	else  syslog_notice('error creating symlink');
	ensureLinkOwnership($linkName,$userName);
}

function ensureLinkOwnership($linkName,$userName)
{
	$lstat=lstat($linkName);
	if($lstat==null)
	{
		syslog_notice("Cannot lstat symlink '$linkName'");
		return;
	}

	$owner=ownerByUid($lstat['uid']);
	$group=groupByGid($lstat['gid']);

	if($owner!=$userName || $group!=$userName) 
	{
		shell_exec("chown --no-dereference $userName.$userName '$linkName'");
		syslog_notice("changed ownership of '$linkName' to '$userName.$userName'");
	}
}

function verifyProjectLink($user,$sharingUser,$project)
{
	$linkName="{$sharingUser->homeFolder}/$project";
	$target="{$user->homeFolder}/$project";
	if(is_link($linkName))
	{
		$currentTarget=readlink($linkName);
		if($currentTarget!=$target)
		{
			syslog_notice("Target:'$currentTarget' is different. Removing existing target.");
			unlink($linkName);
			createSymLink($linkName,$target,$sharingUser->name);
		}
		else
		{
			ensureLinkOwnership($linkName,$sharingUser->name);
		}
	}
	else
	{
		createSymLink($linkName,$target,$sharingUser->name);
	}
}

function purgeProjectLinks($users)
{
	foreach($users as $user)
	{
		$projectLinks=userProjectLinks($user->homeFolder);
		foreach($projectLinks as $projectLink)
			purgeProjectLink($user->name,$user->homeFolder,$projectLink,$users);
	}
}

function isGroupMemberHomeFolder($users,$homeFolder)
{
	foreach($users as $user)
	{
		if($homeFolder==$user->homeFolder) return true;
	}
	return false;
}

function purgeProjectLink($userName,$homeFolder,$projectLink,$users)
{
	$projectLinkPath="$homeFolder/$projectLink";
	$target=readlink($projectLinkPath);

	//if the link does not point to a folder, remove it
	if(!is_dir($target))
	{
		unlink($projectLinkPath);
		syslog_notice("Removed '$projectLinkPath'; target '$target' is not a valid link target");
		return;
	}

	//the link must point to a project folder
	$targetProjectFolder=basename($target);
	if(!isProjectFolder($targetProjectFolder))
	{
		unlink($projectLinkPath);
		syslog_notice("Removed '$projectLinkPath'; in target '$target' ".
			"the target '$targetProjectFolder' is not a valid project folder");
		return;
	}
	//the link must point to member project folder
	$targetHomeFolder=dirname($target);
	if(!isGroupMemberHomeFolder($users,$targetHomeFolder))
	{
		unlink($projectLinkPath);
		syslog_notice("Removed '$projectLinkPath'; in target '$target' ".
			"the home folder '$targetHomeFolder' is not the home folder for a group member");
		return;
	}
}

function reshareAvid($users)
{
	foreach($users as $user)
		reshareAvidFromUser($user,$users);
}

define('MXF_SUBFOLDER','Avid MediaFiles/MXF');

function mxfSubFolders($mxfFolder)
{
	return mxfSubFoldersForType($mxfFolder,'folder');
}

function mxfSubFolderLinks($mxfFolder)
{
	return mxfSubFoldersForType($mxfFolder,'link');
}

function isRequiredMxfSubFolderType($target,$type)
{
	if($type=='folder' && is_link($target)) return false;
	if($type=='link' && !is_link($target)) return false;
	return true;
}

function mxfSubFoldersForType($mxfFolder,$type)
{
	$folders=array();
	if ($handle = opendir($mxfFolder))
	{
		while(false !== ($entry = readdir($handle)))
		{
			$target="$mxfFolder/$entry";
			if(
				!isRejectedFolderEntry($entry)  && 
				isRequiredMxfSubFolderType($target,$type)
			)
			$folders[$entry]=$entry;
		}
		closedir($handle);
	}
	return $folders;
}


function reshareAvidFromUser($user,$users)
{
	$mxfFolder=$user->homeFolder.'/'.MXF_SUBFOLDER;
	if(!file_exists($mxfFolder)) return;
	$folders=mxfSubFolders($mxfFolder);
	foreach($folders as $folder)
	{
		$target="$mxfFolder/$folder";
		foreach($users as $sharingUser)
		{
			if($user->name != $sharingUser->name)
				reshareAvidMXFToUser($sharingUser,$target,$folder,$user->name);
		}
	}
}

function reshareAvidMXFToUser($sharingUser,$target,$entry,$fromUserName)
{
	$mxfSubFolder="{$sharingUser->homeFolder}/".MXF_SUBFOLDER;
	if(!is_dir($mxfSubFolder))
	{	
		$result=mkdir($mxfSubFolder,0777,true);
		if(!$result) syslog_notice("Cannot create folder '$mxfSubFolder'");
		chown($mxfSubFolder,$sharingUser->name);
		if(!$result) syslog_notice("Cannot chown folder '$mxfSubFolder' to {$sharingUser->name}");
		chgrp($mxfSubFolder,$sharingUser->name);
		if(!$result) syslog_notice("Cannot chgrp folder '$mxfSubFolder' to {$sharingUser->name}");
	}
	$linkName="$mxfSubFolder/{$entry}_$fromUserName";
	if(!is_link($linkName))
		createSymlink($linkName,$target,$sharingUser->name);
	ensureLinkOwnership($linkName,$sharingUser->name);
}

function purgeAvid($users)
{
	foreach($users as $user)
		purgeAvidForUser($user,$users);
}


function purgeAvidForUser($user,$users)
{
	$mxfFolder=$user->homeFolder.'/'.MXF_SUBFOLDER;
	if(!file_exists($mxfFolder)) return;
	$folders=mxfSubFolderLinks($mxfFolder);
	foreach($folders as $folder)
	{
		$linkName="$mxfFolder/$folder";
		$target=readlink($linkName);


		//if the link does not point to a folder, remove it
		if(!is_dir($target))
		{
			unlink($linkName);
			syslog_notice("Removed '$linkName'; target '$target' is not a folder");
			return;
		}

		//the link must point to an mxf folder
		$rootFolder=dirname($target);
		if(!endsWith($rootFolder,MXF_SUBFOLDER))
		{
			unlink($linkName);
			syslog_notice("Removed '$linkName'; in target '$target' ".
				"the target is not a valid mxf folder");
			return;
		}

		//the link must point to member project folder
		$targetHomeFolder=dirname(dirname($rootFolder));

		if(!isGroupMemberHomeFolder($users,$targetHomeFolder))
		{
			unlink($linkName);
			syslog_notice("Removed '$linkName'; in target '$target' ".
				"the home folder '$targetHomeFolder' is not the home folder for a group member");
			return;
		}

	}	
}
