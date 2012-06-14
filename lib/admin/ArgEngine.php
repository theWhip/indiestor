<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('args/CommandEntityDefinitions.php');
require_once('args/CommandActionDefinitions.php');
require_once('args/CommandAction.php');
require_once('args/ProgramActions.php');

define('ERRNUM_INVALID_ENTITY_TYPE',11);
define('ERRNUM_MISSING_ENTITY_TYPE',12);
define('ERRNUM_UNEXPECTED_ENTITY_TYPE',13);
define('ERRNUM_DUPLICATE_ACTION',14);
define('ERRNUM_INVALID_ACTION_FOR_ENTITY_TYPE',15);
define('ERRNUM_INCOMPATIBLE_ACTIONS',16);
define('ERRNUM_MISSING_ACTION_ARGUMENT',17);
define('ERRNUM_INVALID_ACTION_ARGUMENT',18);
define('ERRNUM_MISSING_ENTITY_NAME',19);
define('ERRNUM_NO_ACTIONS',20);
define('ERRNUM_MANDATORY_PREREQUISITE_ACTION_MISSING',21);

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
		$this->scriptName=$args[0];
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

	function printStdErr($msg)
	{
		file_put_contents('php://stderr',$msg);
	}

	function argsError($msg,$printUsage,$errNum)
	{
		$this->printStdErr("Error $errNum: $msg.\n");
		if($printUsage) $this->usage();
		exit($errNum);
	}

	function process()
	{
		$this->processEntityType();
		$this->processActions();
		$this->checkEntity();
		$this->checkActions();
		$this->checkMandatoryActions();
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
					$this->argsError("Invalid entity type '$entityType'. ",
						true, ERRNUM_INVALID_ENTITY_TYPE);
				ProgramActions::$entityType=$entityType;
				$this->removeCurrentArg();
				return;
			}
			$this->moveNext();
		}        
		$this->argsError("Missing entity type",true, ERRNUM_MISSING_ENTITY_TYPE);
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
				$this->argsError("Unexpected entity type '$entityType'. ".
					"Already processing entity type '".
					ProgramActions::$entityType."'",false, 
					ERRNUM_UNEXPECTED_ENTITY_TYPE);
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
			$this->argsError("duplicate action '$action'",false,ERRNUM_DUPLICATE_ACTION);
		//check if it is allowed for the current entity type
		if(!$this->commandActionDefinitions->isValidActionForEntityType($entityType,$action))
			$this->argsError("Invalid action '$action' for entity type '$entityType'",
				true, ERRNUM_INVALID_ACTION_FOR_ENTITY_TYPE);
		//check if the action not incompatible with another action
		$firstIncompatibleAction=$this->commandActionDefinitions->firstIncompatibleAction(
						$entityType,ProgramActions::actionArray(),$action);
		if($firstIncompatibleAction!=null)
			$this->argsError("Action '$action' incompatible with '$firstIncompatibleAction' ".
				"for entity type '$entityType'",false,
				ERRNUM_INCOMPATIBLE_ACTIONS);
		//check if action has argument
		$hasArg=$this->commandActionDefinitions->actionHasArg($entityType,$action);
		if($hasArg)
		{
			$this->moveNext();
			if($this->EOArgs())
				$this->argsError(
					"argument expected for action '$action' ".
					"for entity type '$entityType'", true,
					ERRNUM_MISSING_ACTION_ARGUMENT);
			if(!$this->currentArgTypeIsParameter())
				$this->argsError("invalid argument '".$this->currentArg().
					"' for action '$action'".
					"for entity type '$entityType'", true,
					ERRNUM_INVALID_ACTION_ARGUMENT);
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
			$this->argsError("Missing entity name for entity type '$entityType'",true,
					ERRNUM_MISSING_ENTITY_NAME);
	}

	function checkActions()
	{
		$entityType=ProgramActions::$entityType;		
		if(!$this->commandEntityDefinitions->mustHaveActions($entityType)) return;
		if(ProgramActions::$actions==null)
			$this->argsError("No action specified for entity type '$entityType'",true,
					ERRNUM_NO_ACTIONS);
	}

	function checkMandatoryActions()
	{
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
					$this->argsError("Action '$action' for entity type '$entityType' can only be used along ".
							"with '$mandatoryPrerequisiteAction'",
							true, ERRNUM_MANDATORY_PREREQUISITE_ACTION_MISSING);
				}
			}
	        }
	}


	function processEntity()
	{
		$entityName=$this->currentArg();
		//check if entity type expects an entity name
		$entityType=ProgramActions::$entityType;
		if(!$this->commandEntityDefinitions->hasArg($entityType))
			$this->argsError("Unexpected entity '$entityName'. ".
				"Entity type '$entityType' does not take arguments",
				true,ERRNUM_UNEXPECTED_ENTITY_TYPE);			
		//check if entity name has already been processed
		if(ProgramActions::$entityName!=null)
			$this->argsError("Unexpected entity '$entityName'. ".
				"Already processing entity ".ProgramActions::$entityName,
				true,ERRNUM_UNEXPECTED_ENTITY_TYPE);
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

	function showCommands()
	{
		$this->printStdErr("--- Usage ---\n");
		$scriptName=$this->scriptName;
		foreach($this->commandEntityDefinitions->entityDefinitions as $entityDefinition)
		{
			$entityType=$entityDefinition->entityType;
                        if($entityDefinition->hasArg) $hasArg=' <arg>'; else $hasArg='';
			$commandActionDefs=
				$this->commandActionDefinitions->commandsForEntityType($entityType);
			$this->printStdErr("$scriptName --$entityType$hasArg $commandActionDefs\n");
		}
		$this->printStdErr("supported options: [-simulate] [-verbose]\n");
	}
}

