<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/
class PluginLoader
{
	var $plugins=null;

	function load()
	{
		$this->plugins=array();
		$this->requireFolderFilesRecursively('plugins');
		return $this->plugins;
	}

	function isAnnoyingDirEntry($fileName)
	{
		if($fileName=='.') return true;
		if($fileName=='..') return true;
		return false;
	}

	function isValidPluginFileName($fileName)
	{
		if(!preg_match('/.*\.php/',$fileName)) return false;
		return true;
	}

	function requireFolderFilesRecursively($folder,$item='',$level=0)
	{

		if($level==1) 
		{
			//level 1 are plugin folders
			$this->plugins[]=$item;
		}

		$dirPath=dirname(dirname(__FILE__)).'/'.$folder;

	        $dir=dir($dirPath);
	        $entry=$dir->read();
	        while($entry!=false)
	        {
		        if(!$this->isAnnoyingDirEntry($entry))
		        {
			        $filePath="$dirPath/$entry";

			        if(is_dir($filePath))
			        {
				        $this->requireFolderFilesRecursively(
					        "$folder/$entry",$entry,$level+1);
			        }
			        else
			        {
				        if($this->isValidPluginFileName($entry)) 
				        {
					        require_once($filePath);
				        }
			        }
		        }
		        $entry=$dir->read();
	        }
	}
}

