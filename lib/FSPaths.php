<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/
class FSPaths
{
        var $paths=null;

        function __construct()
        {
                $this->paths=array();
        }

        function add($path)
        {
	        $this->paths[]=$path;
        }

	function addPaths($FSPaths)
	{
		foreach($FSPaths->paths as $path)
		{
			$this->add($path);
		}
	}        

	function dump()
	{
                $i=1;
		foreach($this->paths as $path)
		{
			echo $i.') '. $path;
                        $i++;
		}
	}
}

