<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('GroupRepairer.php');
require_once('UserRepairer.php');
require_once('GroupMembershipRepairer.php');

class RepairEngine
{

        function repair($indiestorGroups,$indiestorPreviousGroups)
        {
                self::repairGroups($indiestorGroups,$indiestorPreviousGroups);
                self::repairUsers($indiestorGroups,$indiestorPreviousGroups);
                self::repairGroupMemberships($indiestorGroups,$indiestorPreviousGroups);
        }

        function repairGroups($indiestorGroups,$indiestorPreviousGroups)
        {
                $groupRepairer=new GroupRepairer(
                                self::uniqueGroups($indiestorGroups),
                                self::uniqueGroups($indiestorPreviousGroups));
                $groupRepairer->process();
        }

        function repairUsers($indiestorGroups,$indiestorPreviousGroups)
        {
                $userRepairer=new UserRepairer(
                                self::uniqueUsers($indiestorGroups),
                                self::uniqueUsers($indiestorPreviousGroups));
                $userRepairer->process();
        }

        function repairGroupMemberships($indiestorGroups,$indiestorPreviousGroups)
        {
                $groupMembershipRepairer=new GroupMembershipRepairer(
                                self::uniqueGroupMemberships($indiestorGroups),
                                self::uniqueGroupMemberships($indiestorPreviousGroups));
                $groupMembershipRepairer->process();
        }

        function uniqueGroups($indiestorGroups)
        {
                $groups=array();
                foreach($indiestorGroups as $indiestorGroup)
                {
                        $groups[$indiestorGroup->name]=$indiestorGroup->name;
                }
                return $groups;
        }

        function uniqueGroupMemberships($indiestorGroups)
        {
                $groupMemberships=array();
                foreach($indiestorGroups as $indiestorGroup)
                {
                        foreach($indiestorGroup->members as $member)
                        {
                                $groupMemberships[$indiestorGroup->name.'|'.$member->name]=
                                                        array($indiestorGroup->name,$member->name);
                        }
                }
                return $groupMemberships;
        }

        function uniqueUsers($indiestorGroups)
        {
                $users=array();
                foreach($indiestorGroups as $indiestorGroup)
                {
                        foreach($indiestorGroup->members as $member)
                        {
                                $users[$member->name]=$member->name;
                        }
                }
                return $users;
        }
}

