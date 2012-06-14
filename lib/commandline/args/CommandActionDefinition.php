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
	var $mustSave=null;
	var $isOption=null;

        function __construct($entityType,$action,$hasArg,$priority,$mustSave,$isOption)
        {
                $this->entityType=$entityType;
                $this->action=$action;
                $this->hasArg=$hasArg;
		$this->priority=$priority;
		$this->mustSave=$mustSave;
		$this->isOption=$isOption;
        }

	function newCommandAction($action,$actionArg)
	{
		return new CommandAction
		(
			$action,
			$actionArg,
			$this->priority,
			$this->mustSave,
			$this->isOption
		);
		
	}
}

