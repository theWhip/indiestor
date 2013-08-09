<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

class CommandIncompatibleActionPair
{
        var $entityType=null;
        var $action1=null;
        var $action2=null;

        function __construct($entityType,$action1,$action2)
        {
                $this->entityType=$entityType;
                $this->action1=$action1;
                $this->action2=$action2;
        }
}

