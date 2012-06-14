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
                $this->addActionDefinition("groups","show-all",false);
                $this->addActionDefinition("group","add",false,1);
                $this->addActionDefinition("group","delete",false,1);
                $this->addActionDefinition("group","show-members",false);
                $this->addActionDefinition("group","simulate",false);
                $this->addActionDefinition("group","verbose",false);
                $this->addActionDefinition("users","show-all",false);
                $this->addActionDefinition("user","add",false,1);
                $this->addActionDefinition("user","delete",false,1);
                $this->addActionDefinition("user","set-passwd",true,2);
                $this->addActionDefinition("user","set-home",true,2);
                $this->addActionDefinition("user","remove-home",false,2);
                $this->addActionDefinition("user","add-to-group",true,2);
                $this->addActionDefinition("user","remove-from-group",true,2);
                $this->addActionDefinition("user","simulate",false);
                $this->addActionDefinition("user","verbose",false);
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

        function addActionDefinition($entityType,$action,$hasArg,$priority=9)
        {
                $this->actionDefinitions[$this->syntheticKey($entityType,$action)]=
			new CommandActionDefinition($entityType,$action,$hasArg,$priority);
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

	function actionPriority($entityType,$action)
	{
		if(!$this->isValidActionForEntityType($entityType,$action))
			throw new Exception(
				"invalid action '$action' for entity type '$entityType'");		
		$actionDefinition=$this->actionDefinitions[$this->syntheticKey($entityType,$action)];
		return $actionDefinition->priority;
	}

	function commandsForEntityType($entityType)
	{
		$buffer='';
		foreach($this->actionDefinitions as $actionDefinition)
		{
			if($actionDefinition->entityType==$entityType)
			{
				$buffer.='-'.$actionDefinition->action.' ';
				if($actionDefinition->hasArg) 
				{
					$buffer.='<arg> ';
				}
			}
		}
		return $buffer;
	}
}

