<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once(dirname(dirname(__FILE__)).'/etcfiles/EtcGroup.php');
require_once(dirname(dirname(__FILE__)).'/Shell.php');
require_once('AbstractSetRepairer.php');

class GroupRepairer extends AbstractSetRepairer
{
	var $etcGroup=null;
	var $elements=null;
	var $previousElements=null;


	function __construct($indiestorGroups,$indiestorPreviousGroups)
	{
		$this->etcGroup=new EtcGroup();
		$this->elements=$indiestorGroups;
		$this->previousElements=$indiestorPreviousGroups;
	}

	function deleteElement($indiestorGroupName)
	{
		Shell::exec("delgroup '$indiestorGroupName'");
	}

	function repairElement($indiestorGroupName)
	{
		if(!$this->etcGroup->exists($indiestorGroupName))
		{
        		Shell::exec("addgroup '$indiestorGroupName'");
		}
	}
}

