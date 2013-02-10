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

define('VOLUME_QUOTA_FILE','/etc/indiestor.quota');
define('VOLUME_QUOTA_TMP_FILE_PREFIX','/tmp/indiestor.quota');

class EtcIndiestorQuota
{

	//----------------------------------------------
	// FILE EXISTS
	//----------------------------------------------

        static function fileExists()
        {
                if(file_exists(VOLUME_QUOTA_FILE)) return true;
                else return false;
        }

	//----------------------------------------------
	// VOLUME EXISTS
	//----------------------------------------------

        static function volumeExists($volume)
        {
                if(!self::fileExists()) return false;
		if(ShellCommand::query("grep $volume ".VOLUME_QUOTA_FILE,true)->returnCode==0) return true;
                return false; 
        }

	//----------------------------------------------
	// SORT FILE
	//----------------------------------------------

        static function sortFile()
        {
                if(!self::fileExists()) return;
                $tmp=VOLUME_QUOTA_TMP_FILE_PREFIX.'_'.rand();
                ShellCommand::exec('sort '.VOLUME_QUOTA_FILE.' > '.$tmp);
                self::removeFile();
                copy($tmp,VOLUME_QUOTA_FILE);
        }

	//----------------------------------------------
	// REMOVE FILE
	//----------------------------------------------

        static function removeFile()
        {
                if(!self::fileExists()) return;
                unlink(VOLUME_QUOTA_FILE);
        }

	//----------------------------------------------
	// ADD VOLUME
	//----------------------------------------------

        static function addVolume($volume)
        {
                if(self::volumeExists($volume)) return;
                $file=fopen(VOLUME_QUOTA_FILE, 'a');
                fwrite($file, $volume."\n");
                fclose($file);
                self::sortFile();
        }
 
	//----------------------------------------------
	// REMOVE VOLUME
	//----------------------------------------------

        static function removeVolume($volume)
        {
                if(!self::fileExists()) return;
                if(!self::volumeExists($volume)) return;
                $volumes=file(VOLUME_QUOTA_FILE, 
                        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $file=fopen(VOLUME_QUOTA_FILE, 'w');
                foreach($volumes as $volumeSaved)
                        if($volumeSaved!=$volume) 
                               fwrite($file, $volumeSaved."\n");
                fclose($file);        
                self::sortFile();
        }
}

