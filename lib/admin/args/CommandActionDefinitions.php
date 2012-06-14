<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('CommandActionDefinition.php');
require_once('CommandIncompatibleActionPair.php');
require_once('CommandMandatoryActionPair.php');

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
		
		//volumes
		$this->addOutputAction("volumes","show");
		$this->addPrimaryAction("volumes","purge-fstab-backups");
		$this->addExecOptionDefinitions("volumes");

		//groups
		$this->addOutputAction("groups","show");
		$this->addExecOptionDefinitions("groups");

		//users
		$this->addOutputAction("users","show");
		$this->addExecOptionDefinitions("users");

		//volume
		$this->addPrimaryAction("volume","quota-on");
		$this->addPrimaryAction("volume","quota-off");
		$this->addPrimaryAction("volume","quota-remove");
		$this->addExecOptionDefinitions("volume");

		//group
		$this->addPrimaryAction("group","add");
		$this->addPrimaryAction("group","delete");
		$this->addOutputAction("group","show-members");
		$this->addExecOptionDefinitions("group");

		//user
		$this->addPrimaryAction("user","add");
		$this->addPrimaryAction("user","delete");
		$this->addPrimaryAction("user","expel");
		$this->addSecondaryAction("user","set-home",true);
		$this->addSecondaryAction("user","remove-home",false);
		$this->addSecondaryAction("user","move-home-content",false);
		$this->addSecondaryAction("user","set-group",true);
		$this->addSecondaryAction("user","unset-group",false);
		$this->addSecondaryAction("user","set-quota",true);
		$this->addSecondaryAction("user","remove-quota",false);
                $this->addActionDefinition("user","set-passwd",true,2,false,false);
		$this->addSecondaryAction("user","lock",false);
		$this->addSecondaryAction("user","remove-from-indiestor",false);
		$this->addOutputAction("user","show");
		$this->addExecOptionDefinitions("user");
	}

	function addOutputAction($entity,$action)
	{
                $this->addActionDefinition($entity,$action,false,9,false);
	}

	function addPrimaryAction($entity,$action,$hasArg=false)
	{
                $this->addActionDefinition($entity,$action,$hasArg,1,false);
	}

	function addSecondaryAction($entity,$action,$hasArg=false)
	{
                $this->addActionDefinition($entity,$action,$hasArg,2,false);
	}

	function addExecOptionDefinitions($entity)
	{
                $this->addActionDefinition($entity,"simulate",false,9,true);
                $this->addActionDefinition($entity,"verbose",false,9,true);
	}

	function configureIncompatibleActions()
	{
                $this->incompatibleActions=array();
		$this->addIncompatibleActionPair("volume","quota-on","quota-off");
		$this->addIncompatibleActionPair("volume","quota-on","quota-remove");
		$this->addIncompatibleActionPair("volume","quota-off","quota-remove");
		$this->addIncompatibleActionPair('group','add','delete');
		$this->addIncompatibleActionPair('user','add','delete');
		$this->addIncompatibleActionPair('user','add','remove-home');
		$this->addIncompatibleActionPair('user','add','unset-group');
		$this->addIncompatibleActionPair('user','delete','set-passwd');
		$this->addIncompatibleActionPair('user','delete','set-home');
		$this->addIncompatibleActionPair('user','delete','set-group');
		$this->addIncompatibleActionPair('user','delete','set-quota');
		$this->addIncompatibleActionPair('user','lock','set-passwd');
		$this->addIncompatibleActionPair('user','move-home-content','add');
		$this->addIncompatibleActionPair('user','move-home-content','delete');
		$this->addIncompatibleActionPair('user','move-home-content','remove-home');
		$this->addIncompatibleActionPair('user','set-quota','remove-quota');
		$this->addIncompatibleActionPair('user','delete','unset-group');
		$this->addIncompatibleActionPair('user','delete','remove-quota');
		$this->addIncompatibleActionPair('user','delete','lock');
		$this->addIncompatibleActionPair('user','set-home','remove-home');
		$this->addIncompatibleActionPair('user','set-group','unset-group');
	}

	function configureMandatoryActions()
	{
                $this->mandatoryActions=array();
		$this->addMandatoryActionPair("user",'remove-home','delete');
		$this->addMandatoryActionPair("user",'move-home-content','set-home');
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
		$this->addSingletonAction("user","remove-from-indiestor");
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

	function incompatibleSyntheticKey($entityType,$action1,$action2)
	{
		return $entityType.'|'.$action1.'|'.$action2;
	}

	function mandatorySyntheticKey($entityType,$action1,$action2)
	{
		return $entityType.'|'.$action1.'|'.$action2;
	}

	function addIncompatibleActionPair($entityType,$action1,$action2)
	{
		$incompatibleSyntheticKey=$this->incompatibleSyntheticKey($entityType,$action1,$action2);
		$this->incompatibleActions[$incompatibleSyntheticKey]=
			new CommandIncompatibleActionPair($entityType,$action1,$action2);
	}

        function addActionDefinition($entityType,$action,$hasArg,$priority,$isOption)
        {
                $this->actionDefinitions[$this->syntheticKey($entityType,$action)]=
			new CommandActionDefinition($entityType,$action,$hasArg,
					$priority,$isOption);
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

