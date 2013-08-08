<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

class SharingFolders
{

	static function endsWith($str, $needle)
	{
		$length = strlen($needle);
		$result=!$length || substr($str, - $length) === $needle;
	   	return $result;
	}

	static function isProjectFolder($folder)
	{
		if(self::isDefaultProjectFolder($folder)) return true;
		if(self::isAvidProjectFolder($folder)) return true;
		return false;
	}

	static function isDefaultProjectFolder($folder)
	{
		if(self::endsWith($folder,'.shared')) return true;
		return false;
	}

	static function isAvidProjectFolder($folder)
	{
		if(self::endsWith($folder,'.avid')) return true;
		return false;
	}

	static function isAvidProjectCopyFolder($folder)
	{
		if(self::endsWith($folder,'.copy')) return true;
		return false;
	}

	static function userRenamedProjectFolders($homeFolder)
	{
		$subFolders=self::userSubFolders($homeFolder);
		$projects=array();
		foreach($subFolders as $subFolder)
		{
			if(!self::isProjectFolder($subFolder) &&
				is_dir("$homeFolder/$subFolder") &&
				!is_link("$homeFolder/$subFolder") &&
				$subFolder!='Avid MediaFiles' &&
				$subFolder!='Avid Shared Projects' &&
				$subFolder[0]!='.') //don't deal with hidden folders
					if(file_exists("$homeFolder/$subFolder/Shared"))
						$projects[$subFolder]=$subFolder;
		}
		return $projects;
	}

	static function userProjects($homeFolder)
	{
		$subFolders=self::userSubFolders($homeFolder);
		$projects=array();
		foreach($subFolders as $subFolder)
		{
			if(self::isProjectFolder($subFolder) &&
				is_dir("$homeFolder/$subFolder") &&
				!is_link("$homeFolder/$subFolder"))
			$projects[$subFolder]=$subFolder;
		}
		return $projects;
	}

	static function userAvidProjects($homeFolder)
	{
		$avidProjects=array();
		$projects=self::userProjects($homeFolder);
		foreach($projects as $project)
		{
			if(self::isAvidProjectFolder($project))
				$avidProjects[$project]=$project;
		}
		return $avidProjects;
	}

	static function userdefaultProjects($homeFolder)
	{
		$defaultProjects=array();
		$projects=self::userProjects($homeFolder);
		foreach($projects as $project)
		{
			if(self::isDefaultProjectFolder($project))
				$defaultProjects[$project]=$project;
		}
		return $defaultProjects;
	}

	static	function isRejectedFolderEntry($entry)
	{
		if($entry=='.') return true;
		if($entry=='..') return true;
		return false;
	}

	static function userSubFolders($folder)
	{
		$subFolders=array();
		if(!file_exists($folder)) return $subFolders;
		if(!is_dir($folder)) return $subFolders;

		if ($handle = opendir($folder))
		{
			while(false !== ($entry = readdir($handle)))
			{
				if(!self::isRejectedFolderEntry($entry))
					$subFolders[$entry]=$entry;
			}
			closedir($handle);
		}
		return $subFolders;
	}

	static function userProjectLinks($homeFolder)
	{
		$subFolders=self::userSubFolders($homeFolder);
		$projectLinks=array();
		foreach($subFolders as $subFolder)
		{
			if((self::isProjectFolder($subFolder) || self::isAvidProjectCopyFolder($subFolder)) &&
				is_dir("$homeFolder/$subFolder") &&
				is_link("$homeFolder/$subFolder"))
			$projectLinks[$subFolder]=$subFolder;
		}
		return $projectLinks;
	}

	static function userdefaultProjectLinks($homeFolder)
	{
		$defaultProjectLinks=array();
		$projectLinks=self::userProjectLinks($homeFolder);
		foreach($projectLinks as $projectLink)
		{
			if(self::isDefaultProjectFolder($projectLink))
				$defaultProjectLinks[$project]=$project;
		}
		return $defaultProjectLinks;
	}

	static function userAvidProjectLinks($homeFolder)
	{
		$avidProjectLinks=array();
		$projectLinks=self::userProjectLinks($homeFolder);
		foreach($projectLinks as $projectLink)
		{
			if(self::isAvidProjectFolder($projectLink))
				$avidProjectLinks[$project]=$project;
		}
		return $avidProjectLinks;
	}

	static function isGroupMemberHomeFolder($users,$homeFolder)
	{
		foreach($users as $user)
		{
			if($homeFolder==$user->homeFolder) return true;
		}
		return false;
	}

        static function folderHasValidAVPfile($folder)
        {
                $nonEmptyAVPfileCount=0;
		if ($handle = opendir($folder))
		{
			while(false !== ($entry = readdir($handle)))
			{
				$file="$folder/$entry";
				if(is_file($file))
					if(SharingFolders::endsWith($entry,'.avp'))
                                                if(filesize($file)>0)
                                                        $nonEmptyAVPfileCount++;
			}
			closedir($handle);
		}
                #if a non-empty AVP file is present, no need to deal with empty AVP files
                if($nonEmptyAVPfileCount>0) return true;
                return false;
        }

}

