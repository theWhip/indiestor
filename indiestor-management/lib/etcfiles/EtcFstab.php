<?php

/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once(dirname(dirname(__FILE__)).'/sysqueries/ShellQuery.php');
require_once(dirname(dirname(__FILE__)).'/sysqueries/all.php');

class EtcOneFileSystem
{
	var $lineNumber=null;
	var $line=null;
	var $_1_fs_spec=null;
	var $_2_fs_file=null;
	var $_3_fs_vfstype=null;
	var $_4_fs_mntops=null;
	var $_4_fs_mntops_csv=null; //same but comma-separated
	var $_5_fs_freq=null;
	var $_6_fs_passno=null;
}

class EtcFsTab
{
	static $instance=null;	
	var $fileSystems=null;

	//----------------------------------------------
	// INSTANCE
	//----------------------------------------------

	static function instance()
	{
		if(self::$instance==null) self::$instance=new EtcFsTab();
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
		$etcFstabFile=sysquery("nl /etc/fstab | grep -v '#'");
                $this->fileSystems=$this->parseEtcFstabFile($etcFstabFile);
	}

	function removeLineNumber($line)
	{
		return trim(preg_replace('/^\d+/','',$line));
	}

	function parseEtcFstabFile($etcFstabFile)
	{
		$fileSystems=array();
		$fileSystemArray=explode("\n",$etcFstabFile);
		foreach($fileSystemArray as $fileSystemLine)
		{
			$fileSystemLineOriginal=$fileSystemLine;
			//remove line number
			$fileSystemLineOriginal=trim($fileSystemLineOriginal);
			$fileSystemLineOriginal=$this->removeLineNumber($fileSystemLineOriginal);
			if($fileSystemLine!='')
			{
				$fileSystemLine=preg_replace('/ +/',' ',$fileSystemLine);
				$fileSystemLine=preg_replace('/\t/',' ',$fileSystemLine);
				$fileSystemLine=trim($fileSystemLine);
				$fileSystemLineFields=explode(' ',$fileSystemLine);
				$fileSystem=new EtcOneFileSystem();
				$fileSystem->lineNumber=$fileSystemLineFields[0];
				$fileSystem->line=$fileSystemLineOriginal;
				$fileSystem->_1_fs_spec=$fileSystemLineFields[1];
				$fileSystem->_2_fs_file=$fileSystemLineFields[2];
				$fileSystem->_3_fs_vfstype=$fileSystemLineFields[3];
				$fileSystem->_4_fs_mntops_csv=$fileSystemLineFields[4];
				$fileSystem->_4_fs_mntops=explode(',',$fileSystemLineFields[4]);
				$fileSystem->_5_fs_freq=$fileSystemLineFields[5];
				$fileSystem->_6_fs_passno=$fileSystemLineFields[6];
				$fileSystems[]=$fileSystem;
			}
		}
		return $fileSystems;
	}

	//----------------------------------------------
	// FIND FS TAB FILE SYSTEM FOR DEVICE
	//----------------------------------------------
	function findFstabFileSystemForDevice($device)
	{
		foreach($this->fileSystems as $fileSystem)
		{
			if($fileSystem->_1_fs_spec==$device) return $fileSystem;
		}
		return null; //not found
	}

	//----------------------------------------------
	// FIND FILE SYSTEM FOR DEVICE
	//----------------------------------------------
	function findFileSystemForDevice($device)
	{
		$fileSystem=$this->findFstabFileSystemForDevice($device);
		if($fileSystem==null)
		{
			//look up its UUID
			$deviceUUID=sysquery_blkid($device);
			//cannot resolve UUID for this device
			if($deviceUUID==null) return 'no-uuid';
			$fs_spec_alt="UUID=$deviceUUID"; //alternative fs_spec
			$fileSystem=$this->findFstabFileSystemForDevice($fs_spec_alt);
			//cannot resolve fstab filesystem for this UUID
			if($fileSystem==null) return 'no-filesystem-for-uuid';
		}
		return $fileSystem;
	}

	//----------------------------------------------
	// FILESYSTEM HAS ETC FSTAB QUOTA ENABLED
	//----------------------------------------------
	function fileSystemHasEtcFstabQuotaEnabled($fileSystem)
	{
		$usrQuotaEnabled=$this->fileSystemHasEtcFstabTypeQuotaEnabled($fileSystem,'usrquota');
		$grpQuotaEnabled=$this->fileSystemHasEtcFstabTypeQuotaEnabled($fileSystem,'grpquota');
		if($usrQuotaEnabled && $grpQuotaEnabled) return true;
		return false;
	}

	//----------------------------------------------
	// FILESYSTEM HAS ETC FSTAB TYPE QUOTA ENABLED
	//----------------------------------------------
	function fileSystemHasEtcFstabTypeQuotaEnabled($fileSystem,$type)
	{
		$quotaEnabled=false;
		$fsOptions=$fileSystem->_4_fs_mntops;
		foreach($fsOptions as $fsOption)
		{
			if($fsOption==$type) $quotaEnabled=true;
		}
		return $quotaEnabled;
	}

	//----------------------------------------------
	// FILESYSTEM ENABLE ETC FSTAB QUOTA
	//----------------------------------------------
	function fileSystemEnableEtcFstabQuota($fileSystem)
	{
		$usrQuotaEnabled=$this->fileSystemHasEtcFstabTypeQuotaEnabled($fileSystem,'usrquota');
		$grpQuotaEnabled=$this->fileSystemHasEtcFstabTypeQuotaEnabled($fileSystem,'grpquota');
		if(!$usrQuotaEnabled) $fileSystem->_4_fs_mntops[]='usrquota';
		if(!$grpQuotaEnabled) $fileSystem->_4_fs_mntops[]='grpquota';
		$this->writeEtcFstabFileSystem($fileSystem);
	}

	//----------------------------------------------
	// WRITE ETC FSTAB FILE SYSTEM
	//----------------------------------------------
	function writeEtcFstabFileSystem($fileSystem)
	{
		$lineNumber=$fileSystem->lineNumber;
		$old_fs_mntops=$fileSystem->_4_fs_mntops_csv;
		$new_fs_mntops=implode(',',$fileSystem->_4_fs_mntops);
		$oldLine=$fileSystem->line;
		$patternToReplace='/'.preg_quote($old_fs_mntops,'/').'/';
		$newLine=preg_replace($patternToReplace,$new_fs_mntops,$oldLine);
		$this->fileReplaceLine($lineNumber,$newLine);
	}

	//----------------------------------------------
	// FILE REPLACE LINE
	//----------------------------------------------
	function fileReplaceLine($lineNumber,$newLine)
	{
		$lines=file('/etc/fstab');
		$lines[$lineNumber-1]=$newLine."\n";
		$newContent=implode('',$lines);
		file_put_contents('/etc/fstab',$newContent);
	}
}

