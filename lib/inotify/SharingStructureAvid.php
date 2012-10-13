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
		self::purgeProjectLinks($users);
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
				self::verifyProjectArchive($user,$project);
			}
		}
	}

	static function verifyProjectArchive($user,$project)
	{
		#remove archive, if needed
		$homeFolder=$user->homeFolder;
		$projectFolder=$homeFolder."/".$project;
		$archived="$projectFolder/Archived";
		if(is_dir($archived)) 
		{
			//check if archive is empty
			$numberOfItems=intval(shell_exec("ls '$archived' | wc -l"));
			if($numberOfItems==0) shell_exec("rm -rf '$archived'");
		}
	}

	static function verifyProject($groupName,$user,$project,$users)
	{
		self::verifyProjectFiles($user,$project);
		self::verifyProjectSharedFolder($groupName,$user,$project,$users);
	}

	static function verifyProjectSharedFolder($groupName,$user,$project,$users)
	{
		$userName=$user->name;
		$homeFolder=$user->homeFolder;
		$projectFolder=$homeFolder."/".$project;
		$shared="$projectFolder/Shared";

		if(!is_dir($shared) && !file_exists($shared)) mkdir($shared);
		SharingOperations::fixFsObjectPermissions($shared,"755");

		#the owner's own shared subfolder
		$sharedSubOwner="$shared/$userName";

		#owner's archive, if it exists
		$archived="$projectFolder/Archived";
		$archivedOwner="$archived/$userName";
		if(!is_dir($sharedSubOwner)) 
		{
			if(is_dir($archivedOwner))
				rename($archivedOwner, $sharedSubOwner);
			else
				if(!file_exists($sharedSubOwner)) mkdir($sharedSubOwner);
		}
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

	static function verifyProjectFiles($user,$project)
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
		SharingOperations::fixUserObjectOwnership('root',$aspFolder);
		SharingOperations::fixFsObjectPermissions($aspFolder,"755");

		#the user's project.copy folder
		$projectCopy=self::folderAvidToCopy($project);
		$prjCopyFolder="$aspFolder/$projectCopy";
		if(!is_dir($prjCopyFolder)) mkdir($prjCopyFolder);
		SharingOperations::fixProjectFsObjectOwnership($groupName,$user->name,$prjCopyFolder);
		SharingOperations::fixFsObjectPermissions($prjCopyFolder,"750");

		#copy avp and avs files
		self::copyAvidProjectFiles("{$owner->homeFolder}/$project",$prjCopyFolder,$user->name);

		#the user's shared folder
		$shared="$prjCopyFolder/Shared";
		if(!is_dir($shared)) mkdir($shared);
		SharingOperations::fixProjectFsObjectOwnership($groupName,$user->name,$shared);
		SharingOperations::fixFsObjectPermissions($shared,"755");

		#the link from the project owner
		$sharedSubOwner="$shared/{$owner->name}";
		$target="{$owner->homeFolder}/$project/Shared/{$owner->name}";
		SharingOperations::verifySymLink($sharedSubOwner,$target,$user->name);		

		#the user's own shared subfolder
		$sharedSubUser="$shared/{$user->name}";
		if(!is_dir($sharedSubUser))
		{
			$archived="{$owner->homeFolder}/$project/Archived";
			$archivedUser="$archived/{$user->name}";
			if(!is_dir($archivedUser))
				mkdir($sharedSubUser);
			else
			{
				rename($archivedUser,$sharedSubUser);
				shell_exec("chown -R {$user->name}.{$user->name} '$sharedSubUser'");
			}
		}

		SharingOperations::fixProjectFsObjectOwnership($groupName,$user->name,$sharedSubUser);
		SharingOperations::fixFsObjectPermissions($sharedSubUser,"755");

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
		foreach($users as $user)
		{
			self::purgeOldProjectsForUser($user);
			self::purgeInvalidSymlinksInProjects($user,$users);
			self::purgeInvalidSymlinksInAVSFolder($user,$users);
		}
	}

	static function purgeInvalidSymlinksInProjects($user,$users)
	{
		$projects=sharingFolders::userAvidProjects($user->homeFolder);
		foreach($projects as $project)
		{
			$sharedSubFolderRoot="{$user->homeFolder}/$project/Shared";
			$sharedSubFolders=SharingFolders::userSubFolders($sharedSubFolderRoot);
			foreach($sharedSubFolders as $sharedSubFolder)
			{
				$memberFolder="$sharedSubFolderRoot/$sharedSubFolder";
				if(is_link($memberFolder))
				{
					$target=readlink($memberFolder);
					
					//if the link does not point to a folder, remove it
					if(!is_dir($target))
					{
						unlink($memberFolder);
						syslog_notice("Removed '$memberFolder'; target '$target' is not a valid link target");
					}

					//the link must point to member project folder
					$targetHomeFolder=dirname(dirname(dirname(dirname($target))));
					if(!SharingFolders::isGroupMemberHomeFolder($users,$targetHomeFolder))
					{
						if(file_exists($memberFolder)) unlink($memberFolder);
						syslog_notice("Removed '$memberFolder'; in target '$target' ".
							"the home folder '$targetHomeFolder' is not the home folder for a group member");
					}
				}	
			}			
		}
	}

	static function purgeInvalidSymlinksInAVSFolder($user,$users)
	{
		$avpFolder="{$user->homeFolder}/Avid Shared Projects";
		if(!file_exists($avpFolder)) return;
		if(!is_dir($avpFolder)) return;
		$copyFolders=SharingFolders::userSubFolders($avpFolder);
		foreach($copyFolders as $copyFolder)
		{
			$sharedSubFolderRoot="$avpFolder/$copyFolder/Shared";
			$sharedSubFolders=SharingFolders::userSubFolders($sharedSubFolderRoot);
			foreach($sharedSubFolders as $sharedSubFolder)
			{
				$memberFolder="$sharedSubFolderRoot/$sharedSubFolder";
				if(is_link($memberFolder))
				{
					$target=readlink($memberFolder);
					
					//if the link does not point to a folder, remove it
					if(!is_dir($target))
					{
						unlink($memberFolder);
						syslog_notice("Removed '$memberFolder'; target '$target' is not a valid link target");
					}

					//the link must point to member project folder
					$targetHomeFolder=dirname(dirname(dirname(dirname($target))));
					if(!SharingFolders::isGroupMemberHomeFolder($users,$targetHomeFolder))
					{
						if(file_exists($memberFolder)) unlink($memberFolder);
						syslog_notice("Removed '$memberFolder'; in target '$target' ".
							"the home folder '$targetHomeFolder' is not the home folder for a group member");
					}
				}	
			}			
		}
	}

	static function purgeOldProjectsForUser($user)
	{
		$oldProjectFolders=SharingFolders::userRenamedProjectFolders($user->homeFolder);
		foreach($oldProjectFolders as $oldProjectFolder)
		{
			self::verifyProjectFiles($user,$oldProjectFolder);
			self::purgeOldProjectForUser($user,$oldProjectFolder);
		}
	}
	
	static function purgeOldProjectForUser($user,$oldProjectFolder)
	{
		//create archive folder
		$archiveFolder="{$user->homeFolder}/$oldProjectFolder/Archived";
		if(!is_dir($archiveFolder) && !file_exists($archiveFolder)) mkdir($archiveFolder);
		SharingOperations::fixUserObjectOwnership($user->name,$archiveFolder);

		//handle shared subfolders
		$sharedSubFolderRoot="{$user->homeFolder}/$oldProjectFolder/Shared";
		$sharedSubFolders=SharingFolders::userSubFolders($sharedSubFolderRoot);
		foreach($sharedSubFolders as $sharedSubFolder)
		{
			//move content to archive
			$pathSharedSubFolder="$sharedSubFolderRoot/$sharedSubFolder";
			$subArchiveFolder="$archiveFolder/$sharedSubFolder";
			if(is_link($pathSharedSubFolder))
			{
				$islink=true;
				$source=readlink($pathSharedSubFolder);
			}
			else
			{
				$islink=false;
				$source=$pathSharedSubFolder;
			}
			if(file_exists($source)) rename($source,$subArchiveFolder);
			shell_exec("chown -R {$user->name}.{$user->name} $subArchiveFolder");

			//purge copy
			if($islink)
			{
				$copy=dirname(dirname($source));
				$rootOfCopy=dirname($copy);
				$baseOfCopy=basename($rootOfCopy);
				if($baseOfCopy=='Avid Shared Projects')
				{
					//remove copy of project
					shell_exec("rm -rf '$copy'");
					//check if this is the last copy
					$numberOfItems=intval(shell_exec("ls '$rootOfCopy' | wc -l"));
					if($numberOfItems==0) shell_exec("rm -rf '$rootOfCopy'");
				}
			}


		}

		//purge shared folder
		shell_exec("rm -rf '$sharedSubFolderRoot'");
	}

	static function renameUserAvidProjects($user)
	{
		$renameOps=array();
		$projects=sharingFolders::userAvidProjects($user->homeFolder);
		foreach($projects as $project)
		{
			$projectTmp="__{$project}__tmp__";
			rename("{$user->homeFolder}/$project","{$user->homeFolder}/$projectTmp");
			$renameOps[]=array('tmp'=>$projectTmp,'project'=>$project);
		}
		return $renameOps;
	}

	static function renameBackUserAvidProjects($user,$renameOps)
	{
		foreach($renameOps as $renameOp)
		{
			$projectTmp=$renameOp['tmp'];
			$project=$renameOp['project'];
			rename("{$user->homeFolder}/$projectTmp","{$user->homeFolder}/$project");
			self::verifyProjectFiles($user,$project);
		}
	}

	static function archiveASPFolder($user)
	{
		$aspFolder="{$user->homeFolder}/Avid Shared Projects";
		$copyFolders=SharingFolders::userSubFolders($aspFolder);
		foreach($copyFolders as $copyFolder)
		{
			$projectPrefix=basename($copyFolder,'.copy');
			$sharedFolder="$aspFolder/$copyFolder/Shared";
			$members=SharingFolders::userSubFolders($sharedFolder);
			$ownFolder=null;
			$ownerName=null;
			foreach($members as $member)
			{
				$folder="$sharedFolder/$member";
				if($member==$user->name) $ownFolder=$folder;
				if(is_link($folder)) $target=readlink($folder);
				else $target="";
				$requiredSuffix="$projectPrefix.avid/Shared/$member";
				if(SharingFolders::endsWith($target,$requiredSuffix)) $ownerName=$member;
			}

			if($ownFolder!=null && $ownerName!=null)
			{
				//find owner record
				$etcPasswd=EtcPasswd::instance();
				$owner=$etcPasswd->findUserByName($ownerName);

				//archive

				$archived="{$owner->homeFolder}/$projectPrefix.avid/Archived";

				if(!file_exists($archived))
				{
					mkdir($archived);
					SharingOperations::fixUserObjectOwnership($ownerName,$archived);
				}

				$archiveSubFolder="$archived/{$user->name}";
				rename($ownFolder,$archiveSubFolder);
				shell_exec("chown -R $ownerName.$ownerName '$archiveSubFolder'");
			}
		}
		//delete ASP folder
		shell_exec("rm -rf '{$user->homeFolder}/Avid Shared Projects'");
	}

}

