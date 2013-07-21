<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

requireLibFile('inotify/SharingFolders.php');

class InotifyWatchFolders
{
	static function watchesMain($group)
	{
		$folders=array();
                foreach($group->members as $member)
		{
			$user=EtcPasswd::instance()->findUserByName($member);
			$folders=array_merge($folders,self::watchesMainUser($group,$user));
		}
		return $folders;
	}

        static function isLocatedInValidHomeFolderOfGroupMember($folder,$userName,$groupMembers)
        {
                foreach($groupMembers as $member)
                {
			$etcMember=EtcPasswd::instance()->findUserByName($member);
                        if(preg_match("|^{$etcMember->homeFolder}|",$folder))
                                return true;
                }
                return false;
        }

	static function generateTabWatchTree($folder)
	{
		$watchFolders=array();
              	$folder=preg_replace('/ /','\ ',$folder);
		$searchFilter="\\( ! -regex '.*/\..*' ".
			"-and ! -name 'resource.frk' ".
			"-and ! -regex '.*/Statistics' ".
			"-and ! -regex '.*/SearchData'  \\)";
		$folders=ShellCommand::query("find $folder -type d $searchFilter");
		$folders=explode("\n",$folders);
		foreach($folders as $folder)
		{
			$folder=trim($folder);
			if($folder!="")
				$watchFolders[]=$folder;
		}
		return $watchFolders;
	}

	static function watchesMainUser($group,$user)
	{
		$watchFolders=array();
		$homeFolder=$user->homeFolder;
		$watchFolders[]=$homeFolder;
		$avidFolders=SharingFolders::userAvidProjects($homeFolder);
		foreach($avidFolders as $avidFolder)
		{
			$watchFolders[]="$homeFolder/$avidFolder";
                        $sharedFolders=SharingFolders::userSubFolders("$homeFolder/$avidFolder/Shared");
                        foreach($sharedFolders as $sharedFolder)
			{
				$folder="$homeFolder/$avidFolder/Shared/$sharedFolder";
                                if(!is_link($folder))
				{
					$watchFolders=array_merge($watchFolders,self::generateTabWatchTree($folder));
				}
                                else
                                {
                                        $target=readlink($folder);
					if($target!==false && is_dir($target) && 
                                             self::isLocatedInValidHomeFolderOfGroupMember(
								$target,$user->name,$group->members))
                                        {
						$watchFolders=array_merge($watchFolders,
							self::generateTabWatchTree($target));
                                        }
                                }
			}
		}
	
		#watch 'Avid MediaFiles'
		if(file_exists("$homeFolder/Avid MediaFiles")) 
			$watchFolders[]="$homeFolder/Avid MediaFiles";

		#watch 'Avid MediaFiles/MXF'
		if(file_exists("$homeFolder/Avid MediaFiles/MXF")) 
			$watchFolders[]="$homeFolder/Avid MediaFiles/MXF";

		return $watchFolders;
	}

	static function watchesAVP($group)
	{
		$folders=array();
                foreach($group->members as $member)
		{
			$user=EtcPasswd::instance()->findUserByName($member);
			$folders=array_merge($folders,self::watchesAVPUser($group,$user));
		}
		return $folders;
	}

	static function watchesAVPUser($group,$user)
	{
		$watchFolders=array();
		$homeFolder=$user->homeFolder;
		$avidFolders=SharingFolders::userAvidProjects($homeFolder);
		foreach($avidFolders as $avidFolder)
                        if(!SharingFolders::folderHasValidAVPfile("$homeFolder/$avidFolder"))
				$watchFolders[]="$homeFolder/$avidFolder";
		return $watchFolders;
	}
}

