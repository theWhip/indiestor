<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('RepetitionRequest.php');

class Repeater
{
	var $repetitionPattern=null;
	var $pluginFunctionSuffix=null;
	var $plugins=null;
        var $plugin=null;
        var $group=null;
        var $members=null;
        var $member=null;
        var $folders=null;
        var $folder=null;
        var $sharingMember=null;
        var $shareBackMember=null;

        function copy()
        {
                $copy=new Repeater();
		$copy->repetitionPattern=$this->repetitionPattern;
		$copy->pluginFunctionSuffix=$this->pluginFunctionSuffix;
		$copy->plugins=$this->plugins;
                $copy->plugin=$this->plugin;
                $copy->group=$this->group;
                $copy->members=$this->members;
                $copy->member=$this->member;
                $copy->folders=$this->folders;
                $copy->folder=$this->folder;
                $copy->sharingMember=$this->sharingMember;
                $copy->shareBackMember=$this->shareBackMember;
                return $copy;
        }

	//-----------------------------------------------
	// ALWAYS
	//-----------------------------------------------

	function always($repeater)
	{
		return true;
	}

	//-----------------------------------------------
	// SHARING MEMBER DIFFERENT FROM MEMBER
	//-----------------------------------------------

	function sharingMemberDifferentFromMember($repeater)
	{
		if($repeater->member->name!=$repeater->sharingMember->name) 
			return true;
		else return false;
	}

	//-----------------------------------------------
	// SHARE BACK MEMBER DIFFERENT FROM SHARING MEMBER
	//-----------------------------------------------

	function shareBackMemberDifferentFromSharingMember($repeater)
	{
		if($repeater->sharingMember->name!=$repeater->shareBackMember->name)
			return true;
		else return false;
	}

	//-----------------------------------------------
	// MEMBER HAS PLUGIN FOLDERS
	//-----------------------------------------------

	function memberHasPluginFolders($repeater)
	{
       		if(!array_key_exists('folders',$repeater->member)) return false;
		if($repeater->member->folders==null) return false;
               	if(!array_key_exists($repeater->plugin,$repeater->member->folders)) 
			return false;
		return true;
	}

	//-----------------------------------------------
	// APPLY NEXT STEP
	//-----------------------------------------------

	function apply($repetitionRequest)
	{
                //deal with the repetition pattern. It is also part of the name of the function to call.
		if($this->repetitionPattern==null) $repetitionPattern=$repetitionRequest->nextLevel;
		else $repetitionPattern=$this->repetitionPattern.'_'.$repetitionRequest->nextLevel;

		if($repetitionRequest->pluginFunctionSuffix!=null)
		{
                        //execute the plugin runner
			$functionName='pluginRunner';
			$this->pluginFunctionSuffix=$repetitionRequest->pluginFunctionSuffix;
		}
		else
		{
                        //execute the ordinary function
			$functionName='process_'.$repetitionPattern;
		}

		$function=new ReflectionMethod(get_class($this->group),$functionName);

		$collectionName=$repetitionRequest->collectionName;
		$collection=$this->$collectionName;

		foreach($collection as $element)
		{
	                $repeater=$this->copy();

			$elementName=$repetitionRequest->elementName;
	                $repeater->$elementName=$element;
			$repeater->repetitionPattern=$repetitionPattern;
			$conditionFunction=$repetitionRequest->conditionFunction;
			if($this->$conditionFunction($repeater))
			{
		                $function->invoke($this->group,$repeater);
			}
		}
	}
}

