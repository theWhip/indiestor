<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

requireLibFile('admin/args/CommandEntityDefinitions.php');
requireLibFile('admin/args/CommandActionDefinitions.php');
requireLibFile('admin/args/CommandAction.php');
requireLibFile('admin/args/ProgramActions.php');

class ArgEngine
{
	var $scriptName=null;
	var $commandEntityDefinitions=null;
	var $commandActionDefinitions=null;
	var $args=null;
	var $currentIndex=null;

	function __construct($args)
	{
		$this->commandEntityDefinitions=new CommandEntityDefinitions();
		$this->commandActionDefinitions=new CommandActionDefinitions();
		$this->args=$args;
		$this->scriptName='indiestor';
		$this->removeArg(0); //remove calling script
	}

	function argType($arg)
	{
		if(strlen($arg)>0)
			$firstChar=substr($arg,0,1);
		else $firstChar='';

		if(strlen($arg)>1)
			$secondChar=substr($arg,1,1);
		else $secondChar='';
		
		if($firstChar=='-' && $secondChar=='-') return 'entityType';
		else if($firstChar=='-') return 'action';
		else return 'parameter';
	}

	function argsUsageError($messageCode,$parameters=array())
	{
		$errNum=NoticeDefinitions::instance()->usageError($messageCode,$parameters);
		$this->usage();
		exit($errNum);
	}

	function argsError($messageCode,$parameters=array())
	{
		NoticeDefinitions::instance()->error($messageCode,$parameters);
	}

	function process()
	{
		$this->processEntityType();
		$this->processActions();
		$this->checkMandatoryActions();
		$this->checkSingletonActions();
		$this->checkEntity();
		$this->checkActions();
		ProgramActions::sortActionsByPriority();
	}

	function processEntityType()
	{
		$this->resetIndex();
		while(!$this->EOArgs())
		{
			if($this->currentArgTypeIsEntityType())
			{
				$entityType=$this->entityTypeFromCurrentArg();
				if(!$this->commandEntityDefinitions->exists($entityType))
					$this->argsUsageError('ARGS_INVALID_ENTITY_TYPE',array('entityType'=>$entityType));
				ProgramActions::$entityType=$entityType;
				$this->removeCurrentArg();
				return;
			}
			$this->moveNext();
		}        
		$this->argsUsageError('ARGS_MISSING_ENTITY_TYPE');
	}

	function currentArg()
	{
		return $this->args[$this->currentIndex];
	}

	function currentArgType()
	{
		return $this->argType($this->currentArg());
	}

	function currentArgTypeIsEntityType()
	{
		return $this->currentArgType()=='entityType';
	}

	function currentArgTypeIsAction()
	{
		return $this->currentArgType()=='action';
	}

	function currentArgTypeIsParameter()
	{
		return $this->currentArgType()=='parameter';
	}

	function count()
	{
		return count($this->args);
	}

	function resetIndex()
	{
		$this->currentIndex=0;
	}

	function EOArgs()
	{
		if($this->currentIndex>=$this->count()) return true;
		else return false;
	}

	function moveNext()
	{
		$this->currentIndex++;
	}

	function processActions()
	{
		$this->resetIndex();
		while(!$this->EOArgs())
		{
			$this->processCurrentArg();
			$this->moveNext();
		}
	}

	function processCurrentArg()
	{
		if($this->currentArgTypeIsEntityType())
		{
				//error: we are already processing an entity type
				$entityType=$this->EntityTypeFromCurrentArg();
				$this->argsError('ARGS_UNEXPECTED_ENTITY_TYPE', array('unexpectedEntityType'=>$entityType,
							'currentEntityType'=>ProgramActions::$entityType));
		}
		else if($this->currentArgTypeIsAction())
		{
	        	$this->processAction();
		}
		else
		{
			$this->processEntity();
	        }
	}

	function processAction()
	{
		//the entity type has already been determined
		$entityType=ProgramActions::$entityType;

		$action=$this->actionFromCurrentArg();
		//check if it is a duplicate
		if(ProgramActions::actionExists($action))
			$this->argsError('ARGS_DUPLICATE_ACTION', array('action'=>$action));
		//check if it is allowed for the current entity type
		if(!$this->commandActionDefinitions->isValidActionForEntityType($entityType,$action))
			$this->argsUsageError('ARGS_INVALID_ACTION_FOR_ENTITY_TYPE', 
				array('action'=>$action,'entityType'=>$entityType));
		//check if the action not incompatible with another action
		$firstIncompatibleAction=$this->commandActionDefinitions->firstIncompatibleAction(
						$entityType,ProgramActions::actionArray(),$action);
		if($firstIncompatibleAction!=null)
			$this->argsError('ARGS_INCOMPATIBLE_ACTIONS',
				array('entityType'=>$entityType,'action'=>$action,'incompatibleWith'=>$firstIncompatibleAction));
		//check if action has argument
		$hasArg=$this->commandActionDefinitions->actionHasArg($entityType,$action);
		if($hasArg)
		{
			$this->moveNext();
			if($this->EOArgs())
				$this->argsUsageError('ARGS_MISSING_ACTION_ARGUMENT',
					array('entityType'=>$entityType,'action'=>$action));
			if(!$this->currentArgTypeIsParameter())
				$this->argsUsageError('ARGS_UNEXPECTED_ACTION_ARGUMENT',
					array('entityType'=>$entityType,'action'=>$action,'argument'=>$this->currentArg()));
			$actionArg=$this->currentArg();
		}
		else
		{
			$actionArg=null;
		}
		$commandAction=
			$this->commandActionDefinitions->newCommandAction(
						$entityType,$action,$actionArg);
		//Ok. Add it.
		ProgramActions::addAction($commandAction);
	}

	function checkEntity()
	{
		$entityType=ProgramActions::$entityType;
		$entityName=ProgramActions::$entityName;
		if($entityName!=null) return;
		if($this->commandEntityDefinitions->hasArg($entityType))
				$this->argsUsageError('ARGS_MISSING_ENTITY',array('entityType'=>$entityType));
	}

	function checkActions()
	{
		$entityType=ProgramActions::$entityType;		
		if(!$this->commandEntityDefinitions->mustHaveActions($entityType)) return;
		if(ProgramActions::$actions==null)
			$this->argsUsageError('ARGS_ENTITYPE_MISSING_ACTIONS',array('entityType'=>$entityType));
	}

	function checkMandatoryActions()
	{
		//if there are no actions, there cannot be any missing mandatory prerequisite actions
		if(ProgramActions::$actions==null) return;
		if(count(ProgramActions::$actions)==0) return;

		//check for the entity specified
		$entityType=ProgramActions::$entityType;
	        foreach(ProgramActions::$actions as $commandAction)
	        {
	                $action=$commandAction->action;
			//check if the action has a mandatory prerequisite action
			$mandatoryPrerequisiteAction=$this->commandActionDefinitions->mandatoryPrerequisiteAction($entityType,$action);
			if($mandatoryPrerequisiteAction!=null)
			{
				//check if the mandatory prerequisite action is present
				if(!array_key_exists($mandatoryPrerequisiteAction,ProgramActions::$actions))
				{
					$this->argsError('ARGS_MANDATORY_PREREQUISITE_ACTION_MISSING',
						array('entityType'=>$entityType,'action'=>$action,
							'prerequisiteAction'=>$mandatoryPrerequisiteAction));
				}
			}
	        }
	}

	function countOptions()
	{
		$count=0;
	        foreach(ProgramActions::$actions as $commandAction)
	        {
                        if($commandAction->isOption) $count++;
		}
		return $count;
	}

	function checkSingletonActions()
	{
		//if there are no actions, there cannot be any singleton actions
		if(ProgramActions::$actions==null) return;
		if(count(ProgramActions::$actions)==0) return;

		//check for the entity specified
		$entityType=ProgramActions::$entityType;

		//count non-option actions
		$countNonOptionActions=count(ProgramActions::$actions)-self::countOptions();

	        foreach(ProgramActions::$actions as $commandAction)
	        {
	                $action=$commandAction->action;
			if($this->commandActionDefinitions->isSingletonAction($entityType,$action))
			{
				if($countNonOptionActions>1)
					$this->argsError('ARGS_SINGLETON_ACTION',
						array('entityType'=>$entityType,'action'=>$action));
			}
		}
	}


	function processEntity()
	{
		$entityName=$this->currentArg();
		//check if entity type expects an entity name
		$entityType=ProgramActions::$entityType;
		if(!$this->commandEntityDefinitions->hasArg($entityType))
			$this->argsUsageError('ARGS_UNEXPECTED_ENTITY',array('entityType'=>$entityType,'entity'=>$entityName));
		//check if entity name has already been processed
		if(ProgramActions::$entityName!=null)
			$this->argsUsageError('ARGS_DUPLICATE_ENTITY',array('entityType'=>$entityType,
				'unexpectedEntity'=>$entityName,'currentEntity'=>ProgramActions::$entityName));
		ProgramActions::$entityName=$entityName;
	}

	function stripDashes($string,$numDashes)
	{
		return substr($string,$numDashes);
	}

	function entityTypeFromCurrentArg()
	{
		return $this->stripDashes($this->currentArg(),2);
	}
	
	function actionFromCurrentArg()
	{
		return $this->stripDashes($this->currentArg(),1);
	}

	function removeCurrentArg()
	{
		$this->removeArg($this->currentIndex);
	}

	function removeArg($index)
	{
		$newArgs=array();
		$i=0;
		foreach($this->args as $arg)
		{
			if($i!=$index) $newArgs[]=$arg;
			$i++;
		}
		$this->args=$newArgs;
	}

	function usage()
	{
		$this->showCommands();
	}

	function printStdErr($message)
	{
		file_put_contents('php://stderr',$message);
	}

	function showCommands()
	{
		$this->printStdErr("--- Usage ---\n");
		$scriptName=$this->scriptName;
		foreach($this->commandEntityDefinitions->entityDefinitions as $entityDefinition)
		{
			$entityType=$entityDefinition->entityType;
                        if($entityDefinition->hasArg) $hasArg=' <arg>'; else $hasArg='';
			$commandActionDefs=$this->commandActionDefinitions->commandsForEntityType($entityType);
			$this->printStdErr("$scriptName --$entityType$hasArg $commandActionDefs\n");
			foreach($this->commandActionDefinitions->singletonCommandsForEntityType($entityType) as $commandActionDef)
			{
				$this->printStdErr("$scriptName --$entityType$hasArg $commandActionDef\n");
			}
		}
		$this->printStdErr("supported options: [-simulate] [-verbose]\n");
	}
}

