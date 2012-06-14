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
<<<<<<< HEAD
	var $mustSave=null;
	var $isOption=null;

        function __construct($action,$actionArg,$priority,$mustSave,$isOption)
=======
	var $isOption=null;

        function __construct($action,$actionArg,$priority,$isOption)
>>>>>>> added --user -expel and validation fixes
        {
                $this->action=$action;
                $this->actionArg=$actionArg;
		$this->priority=$priority;
<<<<<<< HEAD
		$this->mustSave=$mustSave;
=======
>>>>>>> added --user -expel and validation fixes
		$this->isOption=$isOption;
        }

	function __toString()
	{
		$action=$this->action;
		$actionArg=$this->actionArg;
		$priority=$this->priority;
<<<<<<< HEAD
		$mustSave=$this->mustSave;
=======
>>>>>>> added --user -expel and validation fixes
		$isOption=$this->isOption;

		if($actionArg==null) $argIndication='';
		else $argIndication="with arg $actionArg";

<<<<<<< HEAD
		if($mustSave) $mustSaveIndication="mustSave:yes";
		else $mustSaveIndication='mustSave:no';

		if($isOption) $type="option";
		else $type='action';

		$buffer="$type:$action $argIndication prio:$priority $mustSaveIndication\n";
=======
		if($isOption) $type="option";
		else $type='action';

		$buffer="$type:$action $argIndication prio:$priority\n";
>>>>>>> added --user -expel and validation fixes

		return $buffer;
	}
}

