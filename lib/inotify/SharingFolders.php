<?php
/*
        Indiestor inotify program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
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

	static function userSubFolders($homeFolder)
	{
		$subFolders=array();
		if ($handle = opendir($homeFolder))
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
}

