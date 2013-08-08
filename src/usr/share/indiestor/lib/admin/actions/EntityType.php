<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
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
				ActionEngine::error('AE_ERR_NO_ACTIONS_JUST_OPTIONS');
			}
		}
		else
		{
			if(method_exists($className,'validateUpFront'))
			{
		                $className::validateUpFront();
			}

			$fixSharingStructure=false;
		        foreach(ProgramActions::$actions as $commandAction)
		        {
		                $action=$commandAction->action;
		                $function=actionCamelCaseNameWithFirstLowerCase($action);
		                $className=get_called_class();
		                $className::$function($commandAction);
				if($commandAction->isUpdateCommand)
					ActionEngine::notify($className,$function);
		        }

			if(method_exists($className,'afterCommand'))
			{
		                $className::afterCommand();
			}
		}
        }
}

