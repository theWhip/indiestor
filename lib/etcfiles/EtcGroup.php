<?php

/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class EtcOneGroup
{
	var $name=null;
	var $members=null;

	function findMember($memberName)
	{
		foreach($this->members as $memberInGroup)
		{
			if($memberInGroup==$memberName)
			{
				return $memberInGroup;
			}
		}
		return null;
	}

	function isMember($userName)
	{
		if($this->findMember($userName)!=null) return true;
		else return false;
	}
}

class EtcGroup
{
	static $instance=null;	
	var $groups=null;
	var $indiestorGroup=null;

	//----------------------------------------------
	// INSTANCE
	//----------------------------------------------

	static function instance()
	{
		if(self::$instance==null) self::$instance=new EtcGroup();
		return self::$instance;
	}

	//----------------------------------------------
	// RESET
	//----------------------------------------------

	static function reset()
	{
		self::$instance=null;
	}

	//----------------------------------------------
	// CONSTRUCTOR
	//----------------------------------------------

	function __construct()
	{
		//group_name:password:GID:user_list
		//user_list: a list of the usernames that are members of this group, separated by commas.
                $this->groups=array();
		$etcGroupFile=file_get_contents('/etc/group');
		$groups=$this->parseEtcGroupFile($etcGroupFile);
		$this->findIndiestorUserGroup($groups);
		$this->groups=$this->purge($groups);
	}

	//----------------------------------------------
	// CHECK INDIESTOR USER GROUP PRESENT
	//----------------------------------------------

	function findIndiestorUserGroup($groups)
	{
		foreach($groups as $group)
		{
			if($group->name==ActionEngine::indiestorUserGroup)
			{
				$this->indiestorGroup=$group;
				return;
			}
		}
	}
	//----------------------------------------------
	// PURGE
	//----------------------------------------------

	function purge($groups)
	{
		$newGroups=array();
		foreach($groups as $group)
		{
			if(ActionEngine::isSysGroupIndiestorGroup($group->name))
		        {
		                $group->name=ActionEngine::indiestorGroupName($group->name);
				$newGroups[$group->name]=$group;
		        }
		}
		return $newGroups;
	}

	//----------------------------------------------
	// PARSE GROUP FILE
	//----------------------------------------------

	function parseEtcGroupFile($etcGroupFile)
	{
		$groups=array();
		$etcGroupFileLines=explode("\n",$etcGroupFile);
		foreach($etcGroupFileLines as $etcGroupFileLine)
		{
			if(strlen($etcGroupFileLine)>0)
			{
				$group=$this->parseEtcGroupFileLine($etcGroupFileLine);
				$groups[$group->name]=$group;
			}
		}
		return $groups;
	}

	//----------------------------------------------
	// PARSE GROUP FILE LINE
	//----------------------------------------------

	function parseEtcGroupFileLine($etcGroupFileLine)
	{
		$etcGroupFileLinefields=explode(':',$etcGroupFileLine);
                $name=$etcGroupFileLinefields[0];
	        $oneGroup=new EtcOneGroup();
	        $oneGroup->name=$name;
	        $oneGroup->members=$this->parseEtcGroupFileLineMembers($etcGroupFileLinefields[3]);
	        return $oneGroup;
	}

	//----------------------------------------------
	// PARSE GROUP FILE LINE MEMBERS
	//----------------------------------------------

	function parseEtcGroupFileLineMembers($etcGroupFileLineMemberField)
	{
		$members=array();
		$etcGroupFileLineMembers=explode(',',$etcGroupFileLineMemberField);
		foreach($etcGroupFileLineMembers as $etcGroupFileLineMember)
		{
			if(strlen($etcGroupFileLineMember)>0)
			{
				$members[]=$etcGroupFileLineMember;
			}
		}
		return $members;
	}

	//----------------------------------------------
	// EXISTS
	//----------------------------------------------

	function exists($groupName)
	{
		if($this->findGroup($groupName)!=null) return true;
		else return false;
	}

	//----------------------------------------------
<<<<<<< HEAD
	// FIND GROUP FOR MEMBER
=======
	// FIND GROUP FOR USER
>>>>>>> fixes to error messages; reorganized indiestor subfolders
	//----------------------------------------------
	function findGroupForUserName($userName)
	{
		foreach($this->groups as $group)
		{
			if($group->isMember($userName))
			{
				return $group;
			}
		}
		return null;
	}

	//----------------------------------------------
	// FIND GROUP
	//----------------------------------------------

	function findGroup($groupName)
	{
		foreach($this->groups as $group)
		{
			if($group->name==$groupName)
			{
				return $group;
			}
		}
		return null;
	}
}

