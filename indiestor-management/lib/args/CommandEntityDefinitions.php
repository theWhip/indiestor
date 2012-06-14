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
<<<<<<< HEAD
                $this->add("volumes",false,true);
                $this->add("groups",false,true);
                $this->add("group",true,true);
                $this->add("users",false,true);
                $this->add("user",true,true);
        }        
=======
                $this->addPluralEntityType("volumes");
                $this->addSingularEntityType("volume");
                $this->addPluralEntityType("groups");
                $this->addSingularEntityType("group");
                $this->addPluralEntityType("users");
                $this->addSingularEntityType("user");
        }

	function addPluralEntityType($entityType)
	{
		$this->add($entityType,false,true);
	}

	function addSingularEntityType($entityType)
	{
		$this->add($entityType,true,true);
	}
>>>>>>> added --volumes -show --volume -quota-on -quota-off

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

