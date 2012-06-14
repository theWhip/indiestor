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
}

class EtcGroup
{
	static $instance=null;	
	var $groups=null;

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
		$etcGroupFile=file_get_contents('/etc/group');
		$this->parseEtcGroupFile($etcGroupFile);
	}

	//----------------------------------------------
	// PARSE GROUP FILE
	//----------------------------------------------

	function parseEtcGroupFile($etcGroupFile)
	{
		$etcGroupFileLines=explode("\n",$etcGroupFile);
		foreach($etcGroupFileLines as $etcGroupFileLine)
		{
			if(strlen($etcGroupFileLine)>0)
			{
				$this->parseEtcGroupFileLine($etcGroupFileLine);
			}
		}
	}

	//----------------------------------------------
	// PARSE GROUP FILE LINE
	//----------------------------------------------

	function parseEtcGroupFileLine($etcGroupFileLine)
	{
		$etcGroupFileLinefields=explode(':',$etcGroupFileLine);
		$oneGroup=new EtcOneGroup();
		$oneGroup->name=$etcGroupFileLinefields[0];
		$oneGroup->members=$this->parseEtcGroupFileLineMembers($etcGroupFileLinefields[3]);
		$this->groups[]=$oneGroup;
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

	//----------------------------------------------
	// FIND GROUP MEMBER
	//----------------------------------------------
	
	function findGroupMember($group,$memberName)
	{
		foreach($group->members as $memberInGroup)
		{
			if($memberInGroup==$memberName)
			{
				return $memberInGroup;
			}
		}
		return null;
	}

	//----------------------------------------------
	// IS MEMBER
	//----------------------------------------------

	function isMember($groupName,$userName)
	{
		$group=$this->findGroup($groupName);
		if($group==null) return false;
		$member=$this->findGroupMember($group,$userName);
		if($member==null) return false;
		return true;
	}

}

