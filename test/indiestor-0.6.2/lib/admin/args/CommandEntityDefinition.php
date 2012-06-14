<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class CommandEntityDefinition
{
        var $entityType=null;
        var $hasArg=null;
	var $mustHaveActions=null;

        function __construct($entityType,$hasArg,$mustHaveActions)
        {
                $this->entityType=$entityType;
                $this->hasArg=$hasArg;
		$this->mustHaveActions=$mustHaveActions;
        }
}

