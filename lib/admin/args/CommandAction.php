<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

class CommandAction
{
        var $action=null;
        var $actionArg=null;
	var $priority=null;
	var $isOption=null;
	var $isUpdateCommand=null;

        function __construct($action,$actionArg,$priority,$isOption,$isUpdateCommand)
        {
                $this->action=$action;
                $this->actionArg=$actionArg;
		$this->priority=$priority;
		$this->isOption=$isOption;
		$this->isUpdateCommand=$isUpdateCommand;
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

