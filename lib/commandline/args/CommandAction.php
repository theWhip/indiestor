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
	var $mustSave=null;
	var $isOption=null;

        function __construct($action,$actionArg,$priority,$mustSave,$isOption)
        {
                $this->action=$action;
                $this->actionArg=$actionArg;
		$this->priority=$priority;
		$this->mustSave=$mustSave;
		$this->isOption=$isOption;
        }

	function __toString()
	{
		$action=$this->action;
		$actionArg=$this->actionArg;
		$priority=$this->priority;
		$mustSave=$this->mustSave;
		$isOption=$this->isOption;

		if($actionArg==null) $argIndication='';
		else $argIndication="with arg $actionArg";

		if($mustSave) $mustSaveIndication="mustSave:yes";
		else $mustSaveIndication='mustSave:no';

		if($isOption) $type="option";
		else $type='action';

		$buffer="$type:$action $argIndication prio:$priority $mustSaveIndication\n";

		return $buffer;
	}
}

