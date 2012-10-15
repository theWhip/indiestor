<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

requireLibFile('admin/args/CommandActionDefinition.php');
requireLibFile('admin/args/CommandIncompatibleActionPair.php');
requireLibFile('admin/args/CommandMandatoryActionPair.php');

class CommandActionDefinitions
{
        var $actionDefinitions=null;
	var $incompatibleActions=null;
	var $mandatoryActions=null;
	var $singletonActions=null;

        function __construct()
        {
		$this->configureActionDefinitions();
		$this->configureIncompatibleActions();
		$this->configureMandatoryActions();
		$this->configureSingletonActions();
        }

	function configureActionDefinitions()
	{
                $this->actionDefinitions=array();
		$rows=DefinitionFile::parse('entityActions',array('entityType','action','hasArg',
						'priority','isOption','isUpdateCommand'));
		foreach($rows as $row)
		{
			$this->addActionDefinition($row['entityType'],$row['action'],$row['hasArg'],
						$row['priority'],$row['isOption'],$row['isUpdateCommand']);
		}
	}

        function addActionDefinition($entityType,$action,$hasArg,$priority,$isOption,$isUpdateCommand)
        {
                $this->actionDefinitions[$this->syntheticKey($entityType,$action)]=
			new CommandActionDefinition($entityType,$action,$hasArg,
					$priority,$isOption,$isUpdateCommand);
        }


	function configureIncompatibleActions()
	{
                $this->incompatibleActions=array();
		$rows=DefinitionFile::parse('incompatibleActions',array('entityType','action1','action2'));
		foreach($rows as $row)
		{
			$this->addIncompatibleActionPair($row['entityType'],$row['action1'],$row['action2']);
		}
	}

	function addIncompatibleActionPair($entityType,$action1,$action2)
	{
		$incompatibleSyntheticKey=$this->incompatibleSyntheticKey($entityType,$action1,$action2);
		$this->incompatibleActions[$incompatibleSyntheticKey]=
			new CommandIncompatibleActionPair($entityType,$action1,$action2);
	}

	function incompatibleSyntheticKey($entityType,$action1,$action2)
	{
		return $entityType.'|'.$action1.'|'.$action2;
	}

	function configureMandatoryActions()
	{
                $this->mandatoryActions=array();
		$rows=DefinitionFile::parse('mandatoryPrequisiteActions',array('entityType','action','prerequisite'));
		foreach($rows as $row)
		{
			$this->addMandatoryActionPair($row['entityType'],$row['action'],$row['prerequisite']);
		}
	}

	function addMandatoryActionPair($entityType,$action1,$action2)
	{
		$mandatorySyntheticKey=$this->mandatorySyntheticKey($entityType,$action1,$action2);
		$this->mandatoryActions[$mandatorySyntheticKey]=
			new CommandMandatoryActionPair($entityType,$action1,$action2);
	}

	function configureSingletonActions()
	{
		$this->singletonActions=array();
		$rows=DefinitionFile::parse('singletonActions',array('entityType','action'));
		foreach($rows as $row)
		{
			$this->addSingletonAction($row['entityType'],$row['action']);
		}
	}

	function addSingletonAction($entityType,$action)
	{
		$syntheticKey=$this->syntheticKey($entityType,$action);
		$this->singletonActions[$syntheticKey]=$action;
	}

	function isSingletonAction($entityType,$action)
	{
		$syntheticKey=$this->syntheticKey($entityType,$action);
		if(array_key_exists($syntheticKey,$this->singletonActions)) return true;
		else return false;
	}

	function syntheticKey($entityType,$action)
	{
		return $entityType.'|'.$action;
	}

	function mandatorySyntheticKey($entityType,$action1,$action2)
	{
		return $entityType.'|'.$action1.'|'.$action2;
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

	function mandatoryPrerequisiteAction($entityType,$action)
	{
		foreach($this->mandatoryActions as $mandatoryActionPair)
		{
			if($mandatoryActionPair->action1==$action) return $mandatoryActionPair->action2;
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
		$commandAction=$actionDefinition->newCommandAction($action,$actionArg);
		return $commandAction;
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
			if($actionDefinition->entityType==$entityType 
				&& !$actionDefinition->isOption
				&& !$this->isSingletonAction($entityType,$actionDefinition->action))
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

	function singletonCommandsForEntityType($entityType)
	{
		$actionDefs=array();
		foreach($this->actionDefinitions as $actionDefinition)
		{
			if($actionDefinition->entityType==$entityType 
				&& !$actionDefinition->isOption
				&& $this->isSingletonAction($entityType,$actionDefinition->action))
			{
				$buffer='-'.$actionDefinition->action;
				if($actionDefinition->hasArg) 
				{
					$buffer.=' <arg>';
				}
				$actionDefs[]=$buffer;
			}
		}
		return $actionDefs;
	}

}

