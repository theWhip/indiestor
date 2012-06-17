<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once(dirname(dirname(__FILE__)).'/fspaths/FSPath.php');
require_once(dirname(dirname(__FILE__)).'/fspaths/FSPaths.php');
require_once('GroupDefinition.php');
require_once('MemberDefinition.php');
require_once(dirname(dirname(__FILE__)).'/plugins/PluginLoader.php');

class GroupDefinitions
{
	//-----------------------------------------------
	// VARIABLES
	//-----------------------------------------------

	var $plugins=null;
	var $groupDefinitions=null;
	var $FSPaths=null;

	//-----------------------------------------------
	// CONSTRUCTOR
	//-----------------------------------------------

	function __construct($groups,$memberFolders)
	{
		$pluginLoader=new PluginLoader();
		$this->plugins=$pluginLoader->load();
		$this->mergeGroupsWithProjects($groups,$memberFolders);
		$this->process();
	}

	//-----------------------------------------------
	// MERGE GROUPS WITH PROJECTS
	//-----------------------------------------------

        function mergeGroupsWithProjects($groups,$memberFolders)
        {
                $this->groupDefinitions=array();
                foreach($groups as $group)
                {
                        $groupDefinition=new GroupDefinition();
                        $groupDefinition->name=$group->name;
                        $groupDefinition->members=array();
                        foreach($group->members as $member)
                        {
                                $memberName=$member->name;
                                $groupDefinition->members[]=
                                        $this->findMemberFoldersForMemberName(
							$memberFolders,$memberName);
                        }
                        $this->groupDefinitions[]=$groupDefinition;
                }
        }

	//-----------------------------------------------
	// FIND MEMBER FOLDERS FOR MEMBER NAME
	//-----------------------------------------------

        function findMemberFoldersForMemberName($memberFolders,$memberName)
        {
                foreach($memberFolders as $memberFoldersForMember)
                {
                        if($memberFoldersForMember->name==$memberName)
                                return $memberFoldersForMember;
                }
                //member not found in project definitions ...
                $memberFoldersForMember=new MemberDefinition();
                $memberFoldersForMember->name=$memberName;
                return $memberFoldersForMember; 
        }
	
	//-----------------------------------------------
	// PROCESS
	//-----------------------------------------------

	function process()
	{
		$this->FSPaths=new FSPaths();
		foreach($this->groupDefinitions as $groupDefinition)
		{
			$this->FSPaths->addPaths($groupDefinition->process($this->plugins));
		}
	}
}

