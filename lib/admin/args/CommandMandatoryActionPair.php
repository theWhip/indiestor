<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class CommandMandatoryActionPair
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

