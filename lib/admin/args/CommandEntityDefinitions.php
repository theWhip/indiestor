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
		$rows=DefinitionFile::parse('entityTypes',array('entityType','hasArg','mustHaveActions'));
		foreach($rows as $row)
		{
			$this->add($row['entityType'],$row['hasArg'],$row['mustHaveActions']);
		}
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

