<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once(dirname(dirname(__FILE__)).'/repeater/Repeater.php');

//--------------------
//CARDINALITY
//--------------------
define('FOR_ONE','1');
define('FOR_MANY','x');

//--------------------
//LEVELS
//--------------------
define('MEMBER','ME');
define('FOLDER','FO');
define('SHARING_MEMBER','SM');
define('SHARE_BACK_MEMBER','SB');
define('PLUGIN','PL');
define('AND_THEN','_');

class GroupDefinition
{
        var $name=null;
        var $members=null;
	var $FSPaths=null;

	//-----------------------------------------------
	// PLUGIN RUNNER
	//-----------------------------------------------

	function pluginRunner($repeater)
	{
		$function=$repeater->plugin.'_'.$repeater->pluginFunctionSuffix;
		if(function_exists($function))
		{
	                $this->FSPaths->addPaths($function($repeater));
		}
	}

	//-----------------------------------------------
	// PROCESS
	//-----------------------------------------------

	function process($plugins)
	{
                $repeater=new Repeater();
                $repeater->group=$this;
                $repeater->plugins=$plugins;
		$this->FSPaths=new FSPaths();
                $repeater->members=$this->members;
		$this->process_MEx($repeater);
		return 	$this->FSPaths;
	}

	//-----------------------------------------------
	// PROCESS MEMBERS
	//-----------------------------------------------

	function process_MEx($repeater)
	{
		$repetitionRequest=new RepetitionRequest();
		$repetitionRequest->collectionName='members';
		$repetitionRequest->elementName='member';
		$repetitionRequest->nextLevel=MEMBER.FOR_ONE;
		$repeater->apply($repetitionRequest);

		$repetitionRequest=new RepetitionRequest();
		$repetitionRequest->collectionName='members';
		$repetitionRequest->elementName='member';
		$repetitionRequest->nextLevel=MEMBER.FOR_ONE.AND_THEN.SHARING_MEMBER.FOR_MANY;
		$repeater->apply($repetitionRequest);

		$repetitionRequest=new RepetitionRequest();
		$repetitionRequest->collectionName='members';
		$repetitionRequest->elementName='member';
		$repetitionRequest->nextLevel=MEMBER.FOR_ONE.AND_THEN.FOLDER.FOR_MANY;
		$repeater->apply($repetitionRequest);
	}

	//-----------------------------------------------
	// PROCESS MEMBER
	//-----------------------------------------------

        function process_ME1($repeater)
        {
		$repetitionRequest=new RepetitionRequest();
		$repetitionRequest->collectionName='plugins';
		$repetitionRequest->elementName='plugin';
		$repetitionRequest->nextLevel=PLUGIN.FOR_ONE;
		$repetitionRequest->pluginFunctionSuffix=MEMBER;
		$repeater->apply($repetitionRequest);
        }

	//-----------------------------------------------
	// PROCESS MEMBER SHARING MEMBERS
	//-----------------------------------------------

        function process_ME1_SMx($repeater)
        {
		$repetitionRequest=new RepetitionRequest();
		$repetitionRequest->collectionName='plugins';
		$repetitionRequest->elementName='plugin';
		$repetitionRequest->nextLevel=PLUGIN.FOR_ONE;
		$repeater->apply($repetitionRequest);
        }

	//--------------------------------------------------
	// PROCESS MEMBER SHARING MEMBERS FOR PLUGIN
	//--------------------------------------------------

        function process_ME1_SMx_PL1($repeater)
	{
		$repetitionRequest=new RepetitionRequest();
		$repetitionRequest->collectionName='members';
		$repetitionRequest->elementName='sharingMember';
		$repetitionRequest->nextLevel=SHARING_MEMBER.FOR_ONE;
		$repetitionRequest->conditionFunction='sharingMemberDifferentFromMember';
		$repetitionRequest->pluginFunctionSuffix=MEMBER.AND_THEN.SHARING_MEMBER;
		$repeater->apply($repetitionRequest);
	}

	//-----------------------------------------------
	//PROCESS MEMBER FOLDERS
	//-----------------------------------------------

        function process_ME1_FOx($repeater)
        {
		$repetitionRequest=new RepetitionRequest();
		$repetitionRequest->collectionName='plugins';
		$repetitionRequest->elementName='plugin';
		$repetitionRequest->nextLevel=PLUGIN.FOR_ONE;
		$repetitionRequest->conditionFunction='memberHasPluginFolders';
		$repeater->apply($repetitionRequest);
        }

	//-----------------------------------------------
	//PROCESS MEMBER FOLDERS FOR PLUGIN
	//-----------------------------------------------

        function process_ME1_FOx_PL1($repeater)
        {
		$plugin=$repeater->plugin;
                $repeater->folders=$repeater->member->folders->$plugin;

		$repetitionRequest=new RepetitionRequest();
		$repetitionRequest->pluginFunctionSuffix=MEMBER.AND_THEN.FOLDER;
		$repetitionRequest->collectionName='folders';
		$repetitionRequest->elementName='folder';
		$repetitionRequest->nextLevel=null;
		$repeater->apply($repetitionRequest);

		$repetitionRequest=new RepetitionRequest();
		$repetitionRequest->collectionName='folders';
		$repetitionRequest->elementName='folder';
		$repetitionRequest->nextLevel=FOLDER.FOR_ONE;
		$repeater->apply($repetitionRequest);
        }

	//-----------------------------------------------
	//PROCESS MEMBER FOLDER FOR PLUGIN
	//-----------------------------------------------

        function process_ME1_FOx_PL1_FO1($repeater)
        {
		$repetitionRequest=new RepetitionRequest();
		$repetitionRequest->collectionName='members';
		$repetitionRequest->elementName='sharingMember';
		$repetitionRequest->nextLevel=null;
		$repetitionRequest->conditionFunction='sharingMemberDifferentFromMember';
		$repetitionRequest->pluginFunctionSuffix=MEMBER.AND_THEN.FOLDER.AND_THEN.SHARING_MEMBER;
		$repeater->apply($repetitionRequest);

		$repetitionRequest=new RepetitionRequest();
		$repetitionRequest->collectionName='members';
		$repetitionRequest->elementName='sharingMember';
		$repetitionRequest->nextLevel=SHARING_MEMBER.FOR_ONE;
		$repetitionRequest->conditionFunction='sharingMemberDifferentFromMember';
		$repeater->apply($repetitionRequest);
        }

	//--------------------------------------------------------------
	//PROCESS MEMBER FOLDER FOR PLUGIN FOR SHARING MEMBER
	//--------------------------------------------------------------

        function process_ME1_FOx_PL1_FO1_SM1($repeater)
        {
		$repetitionRequest=new RepetitionRequest();
		$repetitionRequest->collectionName='members';
		$repetitionRequest->elementName='shareBackMember';
		$repetitionRequest->nextLevel=SHARE_BACK_MEMBER.FOR_ONE;
		$repetitionRequest->conditionFunction='shareBackMemberDifferentFromSharingMember';
		$repetitionRequest->pluginFunctionSuffix=MEMBER.AND_THEN.FOLDER.AND_THEN.SHARING_MEMBER.AND_THEN.SHARE_BACK_MEMBER;
		$repeater->apply($repetitionRequest);
        }
}

