<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once(dirname(dirname(__FILE__)).'/etcfiles/EtcGroup.php');
require_once(dirname(dirname(__FILE__)).'/etcfiles/EtcPasswd.php');
require_once(dirname(dirname(__FILE__)).'/Shell.php');

class UserRepairer extends AbstractSetRepairer
{
        var $etcPasswd=null;

        function __construct($indiestorUsers,$indiestorPreviousUsers)
        {
                $this->etcPasswd=new EtcPasswd();
                $this->elements=$indiestorUsers;
                $this->previousElements=$indiestorPreviousUsers;
        }        

	function deleteElement($indiestorUserName)
	{
		Shell::exec("deluser '$indiestorUserName'");
	}

	function repairElement($indiestorUserName)
	{
		if(!$this->etcPasswd->exists($indiestorUserName))
		{
echo("creating user $indiestorUserName\n");
        		Shell::exec("adduser '$indiestorUserName'");
		}
	}
}

