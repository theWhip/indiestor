<?php

/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

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
		$user=$etcPasswdFileLinefields[0];
		$this->users[$user]=$user;
	}

	//----------------------------------------------
	// EXISTS
	//----------------------------------------------

	function exists($userName)
	{
		foreach($this->users as $user)
		{
			if($user==$userName)
			{
				return true;
			}
		}
		return false;
	}

}
