<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('CommandEntityDefinition.php');

class CommandEntityDefinitions
{
        var $entityDefinitions=null;
        
        function __construct()
        {
                $this->entityDefinitions=array();
                $this->add("help",false,false);
                $this->add("sync",false,false);
                $this->add("groups",false,true);
                $this->add("group",true,true);
                $this->add("users",false,true);
                $this->add("user",true,true);
        }        

        function add($entityType,$hasArg,$mustHaveActions)
        {
                $this->entityDefinitions[$entityType]=
			new CommandEntityDefinition($entityType,$hasArg,$mustHaveActions);
        }

	function exists($entityType)
	{
		if(array_key_exists($entityType,$this->entityDefinitions)) return true;
		return false;
	}

	function hasArg($entityType)
	{
		if(!$this->exists($entityType))
			throw new Exception("invalid entity type '$entityType'");
		$commandEntityDefinition=$this->entityDefinitions[$entityType];
		return $commandEntityDefinition->hasArg;
	}

	function mustHaveActions($entityType)
	{
		if(!$this->exists($entityType))
			throw new Exception("invalid entity type '$entityType'");
		$commandEntityDefinition=$this->entityDefinitions[$entityType];
		return $commandEntityDefinition->mustHaveActions;
	}
}

