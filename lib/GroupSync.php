<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('Shell.php');

class GroupSync
{
	var $etcGroup=null;
	var $indiestorGroups=null;
	var $indiestorPreviousGroups=null;


	function __construct($etcGroup, $indiestorGroups,$indiestorPreviousGroups)
	{
		$this->etcGroup=$etcGroup;
		$this->indiestorGroups=$indiestorGroups;
		$this->indiestorPreviousGroups=$indiestorPreviousGroups;
	}

	function process()
	{
		$this->syncDeletedGroups();
		$this->repairGroups();
	}

	function deletedGroups()
	{
		return $this->group1MinusGroup2(
					$this->indiestorPreviousGroups,
					$this->indiestorGroups
			);
	}

	function syncDeletedGroups()
	{
		$deletedGroups=$this->deletedGroups();
		foreach($deletedGroups as $deletedGroup)
		{
			$this->deleteGroup($deletedGroup->name);
		}
	}

	function deleteGroup($indiestorGroupName)
	{
		Shell::exec("delgroup '$indiestorGroupName'");
	}

	function group1MinusGroup2($groups1,$groups2)
	{
		$diff=array();
		foreach($groups1 as $group1)
		{
			if(!$this->groupExists($groups2,$group1))
			{
				$diff[]=$group1;
			}
		}
		return $diff;
	}

	function groupExists($groups,$group1)
	{
		foreach($groups as $group2)
		{
			if($group2->name==$group1->name)
				return true;
		}
		return false;
	}

	function repairGroups()
	{
		foreach($this->indiestorGroups as $indiestorGroup)
		{
			$this->repairGroup($indiestorGroup->name);
		}
	}

	function repairGroup($indiestorGroupName)
	{
		if(!$this->etcGroup->exists($indiestorGroupName))
		{
			$this->createGroup($indiestorGroupName);
		}
	}

	function createGroup($indiestorGroupName)
	{
		Shell::exec("addgroup '$indiestorGroupName'");
	}
}

