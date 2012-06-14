<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class CommandActionDefinition
{
        var $entityType=null;
        var $action=null;
	var $priority=null;
        var $hasArg=null;

        function __construct($entityType,$action,$hasArg=false,$priority=9)
        {
                $this->entityType=$entityType;
                $this->action=$action;
                $this->hasArg=$hasArg;
		$this->priority=$priority;
        }
}

