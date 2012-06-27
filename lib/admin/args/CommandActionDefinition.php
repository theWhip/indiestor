<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('CommandAction.php');

class CommandActionDefinition
{
        var $entityType=null;
        var $action=null;
	var $priority=null;
        var $hasArg=null;
	var $isOption=null;
	var $mustNotify=null;

        function __construct($entityType,$action,$hasArg,$priority,$isOption,$mustNotify)
        {
                $this->entityType=$entityType;
                $this->action=$action;
                $this->hasArg=$hasArg;
		$this->priority=$priority;
		$this->isOption=$isOption;
		$this->mustNotify=$mustNotify;
        }

	function newCommandAction($action,$actionArg)
	{
		return new CommandAction
		(
			$action,
			$actionArg,
			$this->priority,
			$this->isOption,
			$this->mustNotify
		);
		
	}
}

