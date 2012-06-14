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

        function __construct($entityType,$hasArg=false)
        {
                $this->entityType=$entityType;
                $this->hasArg=$hasArg;
        }
}

