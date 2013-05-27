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
requireLibFile('inotify/SharingOperations.php');
requireLibFile('inotify/SharingFolders.php');

define('AMF_SUBFOLDER','Avid MediaFiles');
define('MXF_SUBFOLDER',AMF_SUBFOLDER.'/'.'MXF');

class SharingStructureMXF
{
	static function reshare($users)
	{
		if($users==null) $users=array();

		$avidProjectsPresent=false;
		foreach($users as $user)
		{
			$projects=sharingFolders::userProjects($user->homeFolder);
			$avidProjectsPresent=$avidProjectsPresent || self::hasAtLeastOneAvidProject($projects);
		}

		if($avidProjectsPresent)
			self::reshareAvid($users);

		self::purgeAvid($users);
	}

	static function hasAtLeastOneAvidProject($projects)
	{
		foreach($projects as $project)
		{
			if(SharingFolders::endsWith($project,'.avid')) return true;
		}
		return false;
	}

	static function reshareAvid($users)
	{
		foreach($users as $user)
		{
			self::reshareAvidFromUser($user,$users);
		}
	}

	static function reshareAvidFromUser($user,$users)
	{
		$amfFolder=$user->homeFolder.'/'.AMF_SUBFOLDER;
		$mxfFolder=$user->homeFolder.'/'.MXF_SUBFOLDER;
		if(!file_exists($mxfFolder)) return;

		//retrieve group
		$etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroupForUserName($user->name);

		//if user is member of a group, use his is_ group
		if($group!=null) $groupName=$group->name; else $groupName=$user->name;

		//make sure they have the right group ownership
		SharingOperations::fixProjectFsObjectOwnership($groupName,$user->name,$amfFolder);
		SharingOperations::fixProjectFsObjectOwnership($groupName,$user->name,$mxfFolder);

		$folders=self::mxfSubFolders($mxfFolder);
		foreach($folders as $folder)
		{
			$target="$mxfFolder/$folder";
			foreach($users as $sharingUser)
			{
				if($user->name != $sharingUser->name)
					self::reshareAvidMXFToUser($sharingUser,$target,$folder,$user->name);
			}
		}
	}

	static function reshareAvidMXFToUser($sharingUser,$target,$entry,$fromUserName)
	{
		$mxfSubFolder="{$sharingUser->homeFolder}/".MXF_SUBFOLDER;
		if(!is_dir($mxfSubFolder))
		{	
			$result=mkdir($mxfSubFolder,0777,true);
			if(!$result) syslog_notice("Cannot create folder '$mxfSubFolder'");
			syslog_notice("chown($mxfSubFolder,{$sharingUser->name})");
			chown($mxfSubFolder,$sharingUser->name);
			if(!$result) syslog_notice("Cannot chown folder '$mxfSubFolder' to {$sharingUser->name}");
			syslog_notice("chgrp($mxfSubFolder,{$sharingUser->name}");
			chgrp($mxfSubFolder,$sharingUser->name);
			if(!$result) syslog_notice("Cannot chgrp folder '$mxfSubFolder' to {$sharingUser->name}");
		}
		$linkName="$mxfSubFolder/{$entry}_$fromUserName";
		if(!is_link($linkName))
			SharingOperations::createSymlink($linkName,$target,$sharingUser->name);
		SharingOperations::ensureLinkOwnership($linkName,$sharingUser->name);
	}

	static function purgeAvid($users)
	{
		foreach($users as $user)
			self::purgeAvidForUser($user,$users);
	}


	static function purgeAvidForUser($user,$users)
	{
		$mxfFolder=$user->homeFolder.'/'.MXF_SUBFOLDER;
		if(!file_exists($mxfFolder)) return;
		$folders=self::mxfSubFolderLinks($mxfFolder);
		foreach($folders as $folder)
		{
			$linkName="$mxfFolder/$folder";
			$target=readlink($linkName);
			$rootFolder=dirname($target);
			$targetHomeFolder=dirname(dirname($rootFolder));

			//if the link does not point to a folder, remove it
			if(!is_dir($target))
			{
				unlink($linkName);
				syslog_notice("Removed '$linkName'; target '$target' is not a folder");
			}
			//the link must point to an mxf folder
			elseif(!SharingFolders::endsWith($rootFolder,MXF_SUBFOLDER))
			{
				unlink($linkName);
				syslog_notice("Removed '$linkName'; in target '$target' ".
					"the target is not a valid mxf folder");
			}
			//the link must point to member project folder
			elseif(!SharingFolders::isGroupMemberHomeFolder($users,$targetHomeFolder))
			{
				//file could have been deleted already by a concurrent process
				if(file_exists($linkName))
                                {
                                        unlink($linkName);
        				syslog_notice("Removed '$linkName'; in target '$target' ".
					"the home folder '$targetHomeFolder' is not the home folder for a group member");
                                }
			}

		}	
	}

	static function mxfSubFolders($mxfFolder)
	{
		return self::mxfSubFoldersForType($mxfFolder,'folder');
	}

	static function mxfSubFolderLinks($mxfFolder)
	{
		return self::mxfSubFoldersForType($mxfFolder,'link');
	}

	static function isRequiredMxfSubFolderType($target,$type)
	{
		if(is_file($target)) return false;
		if($type=='folder' && is_link($target)) return false;
		if($type=='link' && !is_link($target)) return false;
		return true;
	}

	static function mxfSubFoldersForType($mxfFolder,$type)
	{
		$folders=array();
		if ($handle = opendir($mxfFolder))
		{
			while(false !== ($entry = readdir($handle)))
			{
				$target="$mxfFolder/$entry";
				if(
					!SharingFolders::isRejectedFolderEntry($entry)  && 
					self::isRequiredMxfSubFolderType($target,$type) &&
					substr($entry,0,1)!='.' && //don't deal with hidden fsobjects
                                        $entry!='resource.frk' //don't deal with these folder/files either (MAC OSX)
				)
				$folders[$entry]=$entry;
			}
			closedir($handle);
		}
		return $folders;
	}
}

