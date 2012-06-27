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
	      	$className=get_called_class();

		if(ProgramActions::$actions==null)
		{
			$function='default_action';

			if(method_exists($className,$function))
			{
		                $className::$function(null);
			}
			else
			{
				ActionEngine::error('no actions specified, just options',
					ERRNUM_NO_ACTIONS_JUST_OPTIONS_SPECIFIED);
			}
		}
		else
		{
			if(method_exists($className,'validateUpFront'))
			{
		                $className::validateUpFront();
			}

		        foreach(ProgramActions::$actions as $commandAction)
		        {
		                $action=$commandAction->action;
		                $function=actionCamelCaseNameWithFirstLowerCase($action);
		                $className=get_called_class();
		                $className::$function($commandAction);
				if($commandAction->mustNotify)
					ActionEngine::notify($className,$function);					
		        }

			if(method_exists($className,'afterCommand'))
			{
		                $className::afterCommand();
			}
		}
        }
}

