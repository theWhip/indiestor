<?php

/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class oneUser
{
	var $name=null;
	var $homeFolder=null;
	var $shell=null;
}

class EtcPasswd
{
	static $instance=null;	
        var $users=null;

	//----------------------------------------------
	// INSTANCE
	//----------------------------------------------

	static function instance()
	{
		if(self::$instance==null) self::$instance=new EtcPasswd();
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
		//username:...other fields ...
		$etcPasswdFile=file_get_contents('/etc/passwd');
		$this->parseEtcPasswdFile($etcPasswdFile);
	}

	//----------------------------------------------
	// PARSE PASSWD FILE
	//----------------------------------------------

        function parseEtcPasswdFile($etcPasswdFile)
        {
		$etcPasswdFileLines=explode("\n",$etcPasswdFile);
		foreach($etcPasswdFileLines as $etcPasswdFileLine)
		{
			if(strlen($etcPasswdFileLine)>0)
			{
				$this->parseEtcPasswdFileLine($etcPasswdFileLine);
			}
		}
                
        }
 
	//----------------------------------------------
	// PARSE PASSWD FILE LINE
	//----------------------------------------------

	function parseEtcPasswdFileLine($etcPasswdFileLine)
	{
		$etcPasswdFileLinefields=explode(':',$etcPasswdFileLine);
		$user=new oneUser();
		$user->name=$etcPasswdFileLinefields[0];
		$user->homeFolder=$etcPasswdFileLinefields[5];
		$user->shell=$etcPasswdFileLinefields[6];
		$this->users[$user->name]=$user;
	}

	//----------------------------------------------
	// EXISTS
	//----------------------------------------------

	function exists($userName)
	{
		return $this->findUserByName($userName)!=null;
	}

	//----------------------------------------------
	// FIND USER BY NAME
	//----------------------------------------------

	function findUserByName($userName)
	{
		foreach($this->users as $user)
		{
			if($user->name==$userName)
			{
				return $user;
			}
		}
		return null;
	}

	//----------------------------------------------
	// FIND USERS FOR ETC GROUP
	//----------------------------------------------

	function findUsersForEtcGroup($group)
	{
		if($group==null) return null;
		if($group->members==null) return null;

		$users=array();
		foreach($group->members as $member)
		{
			$user=$this->findUserByName($member);
			if($user!=null) $users[$member]=$user;			
		}
		return $users;
	}
	
	//----------------------------------------------
	// FIND USER BY HOME FOLDER
	//----------------------------------------------

	function findUserByHomeFolder($homeFolder)
	{
		foreach($this->users as $user)
		{
			if($user->homeFolder==$homeFolder)
			{
				return $user;
			}
		}
		return null;
	}
}
