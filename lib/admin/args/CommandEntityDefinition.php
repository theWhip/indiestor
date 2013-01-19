<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
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

