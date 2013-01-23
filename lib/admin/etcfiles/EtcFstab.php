<?php

/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

requireLibFile('admin/ShellCommand.php');

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

	//----------------------------------------------
	// HAS QUOTA ENABLED
	//----------------------------------------------
	function hasQuotaEnabled()
	{
		return $this->hasQuotaEnabledForType('usrquota');
	}

	//----------------------------------------------
	// HAS QUOTA ENABLED
	//----------------------------------------------
	function hasQuotaEnabledForType($type)
	{
		$quotaEnabled=false;
		$fsOptions=$this->_4_fs_mntops;
		foreach($fsOptions as $fsOption)
		{
			if($fsOption==$type) $quotaEnabled=true;
		}
		return $quotaEnabled;
	}

	//----------------------------------------------
	// ENABLE QUOTA
	//----------------------------------------------
	function enableQuota()
	{
		$usrQuotaEnabled=$this->hasQuotaEnabledForType('usrquota');
		if(!$usrQuotaEnabled) $this->_4_fs_mntops[]='usrquota';
	}

	//----------------------------------------------
	// DISABLE QUOTA
	//----------------------------------------------
	function disableQuota()
	{
		$newMntOps=array();
		foreach($this->_4_fs_mntops as $mntop)
		{
			switch($mntop)
			{
				case 'usrquota': break;
				default: $newMntOps[]=$mntop;
			}
		}
		$this->_4_fs_mntops=$newMntOps;
	}

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
		$etcFstabFile=ShellCommand::query("nl /etc/fstab | grep -v '#'");
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
                                if(count($fileSystemLineFields)<7)
                                {
                                        echo "warning, cannot parse line ".$fileSystemLineFields[0].
                                                "in /etc/fstab: '$fileSystemLine'";
                                        echo "number of fields is ".count($fileSystemLineFields).
                                                "expected at least 7 fields";
                                }
                                else
                                {
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
		}
		return $fileSystems;
	}

	//----------------------------------------------
	// FIND FILE SYSTEM FOR DEVICE OR UUID
	//----------------------------------------------
	function findFileSystemForDeviceOrUUID($deviceOrUUID)
	{
		foreach($this->fileSystems as $fileSystem)
		{
			if($fileSystem->_1_fs_spec==$deviceOrUUID) return $fileSystem;
		}
		return null; //not found
	}

	//----------------------------------------------
	// FIND FILE SYSTEM FOR DEVICE
	//----------------------------------------------
	function findFileSystemForDevice($device)
	{
		$fileSystem=$this->findFileSystemForDeviceOrUUID($device);
		if($fileSystem==null)
		{
			//look up its UUID
			$deviceUUID=sysquery_blkid($device);
			//cannot resolve UUID for this device
			if($deviceUUID==null) return 'no-uuid';
			$fs_spec_alt="UUID=$deviceUUID"; //alternative fs_spec
			$fileSystem=$this->findFileSystemForDeviceOrUUID($fs_spec_alt);
			//cannot resolve fstab filesystem for this UUID
			if($fileSystem==null) return 'no-filesystem-for-uuid';
		}
		return $fileSystem;
	}

	//----------------------------------------------
	// WRITE FILE SYSTEM
	//----------------------------------------------
	function writeFileSystem($fileSystem)
	{
		$this->backup();
		$this->replaceFileSystemLine($fileSystem);
	}

	//----------------------------------------------
	// BACKUP
	//----------------------------------------------
	function backup()
	{
		$currentDateTime=date('Y-m-d_g-i-s');
		$backupFileName="/etc/fstab.backup.$currentDateTime";
		copy('/etc/fstab',$backupFileName);
		NoticeDefinitions::instance()->notice(
			'ETC_FSTAB_BACKUP_CREATED',array('backupFileName'=>$backupFileName));
	}
	//----------------------------------------------
	// WRITE FILE SYSTEM
	//----------------------------------------------
	function replaceFileSystemLine($fileSystem)
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

