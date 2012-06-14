<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class EntityType
{
        static function execute()
        {
		if(ProgramActions::$actions==null)
		{
			$function='default_action';
	                $className=get_called_class();
	                $className::$function(null);
		}
		else
		{
		        foreach(ProgramActions::$actions as $commandAction)
		        {
		                $action=$commandAction->action;
		                $function=ActionEngine::actionCamelCaseNameWithFirstLowerCase($action);
		                $className=get_called_class();
		                $className::$function($commandAction);
		        }
		}
        }
}

