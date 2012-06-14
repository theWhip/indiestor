<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/
require_once('json-fixes.php');
require_once('FSPath.php');
require_once('FSPaths.php');
require_once('GroupDefinition.php');
require_once('plugins/PluginLoader.php');

class MemberDefinition
{
        var $name=null;
	var $folders=null;
}

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

	function __construct()
	{
		$pluginLoader=new PluginLoader();
		$this->plugins=$pluginLoader->load();
	}

	//-----------------------------------------------
	// LOAD DEFINITION FILE
	//-----------------------------------------------

        function loadJSONFile($filePath)
        {
                $data=json_decode_nice($filePath);
                if($data==NULL)
                {
                        echo "ERROR while loading $filePath\n";
		        die(
			        'JSON ERROR:'.json_last_error_description()."\n".
			        "use http://jsonlint.com to validate the json data\n"
		        );
                }
                return $data;
        }

	//-----------------------------------------------
	// LOAD DEFINITION FILES
	//-----------------------------------------------

	function loadFiles($groupsFilePath,$memberFoldersFilePath)
	{
                $groups=$this->loadJSONFile($groupsFilePath);
                $memberFolders=$this->loadJSONFile($memberFoldersFilePath);
                $this->groupDefinitions=$this->mergeGroupsWithProjects($groups,$memberFolders);
	}


	//-----------------------------------------------
	// MERGE GROUPS WITH PROJECTS
	//-----------------------------------------------

        function mergeGroupsWithProjects($groups,$memberFolders)
        {
                $groupDefinitions=array();
                foreach($groups as $group)
                {
                        $groupDefinition=new GroupDefinition();
                        $groupDefinition->name=$group->name;
                        $groupDefinition->members=array();
                        foreach($group->members as $member)
                        {
                                $memberName=$member->name;
                                $groupDefinition->members[]=
                                        $this->findMemberFoldersForMemberName($memberFolders,$memberName);
                        }
                        $groupDefinitions[]=$groupDefinition;
                }

                return $groupDefinitions;
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
                //member not found ...
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

