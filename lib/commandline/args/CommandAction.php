<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class CommandAction
{
        var $action=null;
        var $actionArg=null;
	var $priority=null;

        function __construct($action,$actionArg=null,$priority=9)
        {
                $this->action=$action;
                $this->actionArg=$actionArg;
		$this->priority=$priority;
        }

	function __toString()
	{
		$action=$this->action;
		$actionArg=$this->actionArg;
		$priority=$this->priority;
		if($actionArg==null) return "action=$action ($priority)\n";
		else return "action=$action with arg $actionArg ($priority)\n";
	}
}

