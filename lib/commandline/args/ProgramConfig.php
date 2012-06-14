<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class ProgramConfig
{
	static $entityType=null;
	static $entityName=null;
	static $actions=null;

	static function addAction($commandAction)
	{
		if(self::$actions==null) self::$actions=array();
		self::$actions[$commandAction->action]=$commandAction;
	}

	static function actionArray()
	{
		$actionArray=array();
                if(self::$actions!=null)
                {
		        foreach(self::$actions as $commandAction)
		        {
			        $actionArray[$commandAction->action]=$commandAction->action;
		        }
                }
		return $actionArray;
	}

	static function actionExists($actionName)
	{
		if(self::$actions==null) return false;
		return array_key_exists($actionName,self::$actions);
	}

	static function actionPriorityArray()
	{
		$priorities=array();
                if(self::$actions!=null)
                {
		        foreach(self::$actions as $commandAction)
		        {
			        $priorities[]=$commandAction->priority;
		        }
                }
		return $priorities;
	}

	static function sortActionsByPriority()
	{
                if(self::$actions==null) return;
		$priorities=self::actionPriorityArray();
		array_multisort($priorities,SORT_ASC, self::$actions);
	}

	static function bool2String($bool)
	{
		if($bool) return 'true'; else return 'false';
	}

	static function toString()
	{
		$buffer="--- Program config ---\n";
		$buffer.="entity type: ".self::$entityType."\n";
		$buffer.="entity name: ".self::$entityName."\n";
		$buffer.="actions\n";
                if(self::$actions!=null)
                {
		        foreach(self::$actions as $action)
		        {
			        $buffer.=$action->__toString();
		        }
                }
		return $buffer;
	}
}

