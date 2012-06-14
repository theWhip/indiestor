<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('CommandActionDefinition.php');
require_once('CommandIncompatibleActionPair.php');

class CommandActionDefinitions
{
        var $actionDefinitions=null;
	var $incompatibleActions=null;

        function __construct()
        {
		$this->configureActionDefinitions();
		$this->configureIncompatibleActions();
        }

	function configureActionDefinitions()
	{
                $this->actionDefinitions=array();
		
		//groups
		$this->addOutputAction("groups","show");
		$this->addExecOptionDefinitions("groups");

		//users
		$this->addOutputAction("users","show");
		$this->addExecOptionDefinitions("users");

		//group
		$this->addPrimaryAction("group","add");
		$this->addPrimaryAction("group","delete");
		$this->addOutputAction("group","show-members");
		$this->addExecOptionDefinitions("group");

		//user
		$this->addPrimaryAction("user","add");
		$this->addPrimaryAction("user","delete");
		$this->addSecondaryAction("user","set-home",true);
		$this->addSecondaryAction("user","remove-home",false);
		$this->addSecondaryAction("user","add-to-group",true);
		$this->addSecondaryAction("user","remove-from-group",false);
                $this->addActionDefinition("user","set-passwd",true,2,false,false);
		$this->addOutputAction("user","show");
		$this->addExecOptionDefinitions("user");
	}

	function addOutputAction($entity,$action)
	{
                $this->addActionDefinition($entity,$action,false,9,false,false);
	}

	function addPrimaryAction($entity,$action)
	{
                $this->addActionDefinition($entity,$action,false,1,true,false);
	}

	function addSecondaryAction($entity,$action,$hasArg)
	{
                $this->addActionDefinition($entity,$action,$hasArg,2,true,false);
	}

	function addExecOptionDefinitions($entity)
	{
                $this->addActionDefinition($entity,"simulate",false,9,false,true);
                $this->addActionDefinition($entity,"verbose",false,9,false,true);
	}

	function configureIncompatibleActions()
	{
                $this->incompatibleActions=array();
		$this->addIncompatibleActionPair('group','add','delete');
		$this->addIncompatibleActionPair('user','add','delete');
		$this->addIncompatibleActionPair('user','add','remove-home');
		$this->addIncompatibleActionPair('user','add','remove-from-group');
		$this->addIncompatibleActionPair('user','delete','set-passwd');
		$this->addIncompatibleActionPair('user','delete','set-home');
		$this->addIncompatibleActionPair('user','delete','add-to-group');
	}

	function syntheticKey($entityType,$action)
	{
		return $entityType.'|'.$action;
	}

	function incompatibleSyntheticKey($entityType,$action1,$action2)
	{
		return $entityType.'|'.$action1.'|'.$action2;
	}

	function addIncompatibleActionPair($entityType,$action1,$action2)
	{
		$incompatibleSyntheticKey=$this->incompatibleSyntheticKey($entityType,$action1,$action2);
		$this->incompatibleActions[$incompatibleSyntheticKey]=
			new CommandIncompatibleActionPair($entityType,$action1,$action2);
	}

        function addActionDefinition($entityType,$action,$hasArg,$priority,$mustSave,$isOption)
        {
                $this->actionDefinitions[$this->syntheticKey($entityType,$action)]=
			new CommandActionDefinition($entityType,$action,$hasArg,
					$priority,$mustSave,$isOption);
        }

	function firstIncompatibleAction($entityType,$actions,$newAction)
	{
		foreach($actions as $action)
		{
			if($this->checkIncompatibleActions($entityType,$action,$newAction) ||
			   $this->checkIncompatibleActions($entityType,$newAction,$action))
				return $action;			
		}
		return null;
	}

	function checkIncompatibleActions($entityType,$action1,$action2)
	{
		$incompatibleSyntheticKey=$this->incompatibleSyntheticKey(
						$entityType,$action1,$action2);
		return array_key_exists($incompatibleSyntheticKey,$this->incompatibleActions);
	}

	function isValidActionForEntityType($entityType,$action)
	{
		return array_key_exists($this->syntheticKey($entityType,$action),
						$this->actionDefinitions);		
	}

	function actionHasArg($entityType,$action)
	{
		if(!$this->isValidActionForEntityType($entityType,$action))
			throw new Exception(
				"invalid action '$action' for entity type '$entityType'");		

		$actionDefinition=$this->actionDefinitions[$this->syntheticKey($entityType,$action)];
		return $actionDefinition->hasArg;
	}

	function newCommandAction($entityType,$action,$actionArg)
	{
		if(!$this->isValidActionForEntityType($entityType,$action))
			throw new Exception(
				"invalid action '$action' for entity type '$entityType'");		
		$actionDefinition=$this->actionDefinitions[$this->syntheticKey($entityType,$action)];
		return $actionDefinition->newCommandAction($action,$actionArg);
	}

	function actionPriority($entityType,$action)
	{
		if(!$this->isValidActionForEntityType($entityType,$action))
			throw new Exception(
				"invalid action '$action' for entity type '$entityType'");		
		$actionDefinition=$this->actionDefinitions[$this->syntheticKey($entityType,$action)];
		return $actionDefinition->priority;
	}

	function actionMustSave($entityType,$action)
	{
		if(!$this->isValidActionForEntityType($entityType,$action))
			throw new Exception(
				"invalid action '$action' for entity type '$entityType'");		
		$actionDefinition=$this->actionDefinitions[$this->syntheticKey($entityType,$action)];
		return $actionDefinition->mustSave;
	}

	function commandsForEntityType($entityType)
	{
		$buffer='';
		foreach($this->actionDefinitions as $actionDefinition)
		{
			if($actionDefinition->entityType==$entityType && !$actionDefinition->isOption)
			{
				$buffer.='-'.$actionDefinition->action;
				if($actionDefinition->hasArg) 
				{
					$buffer.=' <arg>';
				}
				$buffer.=' ';
			}
		}
		return $buffer;
	}
}

