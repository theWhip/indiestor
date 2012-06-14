<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

abstract class AbstractSetRepairer
{

        var $elements=null;
	var $previousElements=null;

	abstract function deleteElement($element);
	abstract function repairElement($element);

	function process()
	{
		$this->syncDeletedElements();
		$this->repairElements();
	}

	function deletedElements()
        {
		return $this->element1Minuselement2(
					$this->previousElements,
					$this->elements
			);
        }

	function syncDeletedElements()
	{
		$deletedElements=$this->deletedElements();
		foreach($deletedElements as $deletedElement)
		{
			$this->deleteElement($deletedElement);
		}
	}

	function element1Minuselement2($elements1,$elements2)
	{
		$diff=array();
		foreach($elements1 as $element1)
		{
			if(!array_key_exists($element1,$elements2))
			{
				$diff[]=$element1;
			}
		}
		return $diff;
	}

	function repairElements()
	{
		foreach($this->elements as $element)
		{
			$this->repairElement($element);
		}
	}
}
