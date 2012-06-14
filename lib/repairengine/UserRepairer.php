<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once(dirname(dirname(__FILE__)).'/etcfiles/EtcPasswd.php');
require_once(dirname(dirname(__FILE__)).'/Shell.php');

class UserRepairer extends AbstractSetRepairer
{

        function __construct($indiestorUsers,$indiestorPreviousUsers)
        {
                $this->elements=$indiestorUsers;
                $this->previousElements=$indiestorPreviousUsers;
        }        

	function deleteElement($indiestorUserName)
	{
		Shell::exec("deluser $indiestorUserName");
	}

	function repairElement($indiestorUserName)
	{
		if(!EtcPasswd::instance()->exists($indiestorUserName))
		{
        		Shell::exec("adduser $indiestorUserName");
		}
	}
}

