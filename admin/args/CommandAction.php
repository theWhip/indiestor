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
	var $isOption=null;

        function __construct($action,$actionArg,$priority,$isOption)
        {
                $this->action=$action;
                $this->actionArg=$actionArg;
		$this->priority=$priority;
		$this->isOption=$isOption;
        }

	function __toString()
	{
		$action=$this->action;
		$actionArg=$this->actionArg;
		$priority=$this->priority;
		$isOption=$this->isOption;

		if($actionArg==null) $argIndication='';
		else $argIndication="with arg $actionArg";

		if($isOption) $type="option";
		else $type='action';

		$buffer="$type:$action $argIndication prio:$priority\n";

		return $buffer;
	}
}

