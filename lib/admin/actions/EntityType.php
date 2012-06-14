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
			ActionEngine::error('no actions specified, just options',ERRNUM_NO_ACTIONS_JUST_OPTIONS_SPECIFIED);
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

