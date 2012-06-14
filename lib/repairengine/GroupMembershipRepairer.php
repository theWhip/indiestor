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

class GroupMembershipRepairer extends AbstractSetRepairer
{
        function __construct($indiestorMemberships,$indiestorPreviousMemberships)
        {
                $this->elements=$indiestorMemberships;
                $this->previousElements=$indiestorPreviousMemberships;
        }        

        function splitGroupMemberShip($indiestorGroupMembership)
        {
		$etcGroupMembershipFields=explode("|",$indiestorGroupMembership);
                return array('group'=>$etcGroupMembershipFields[0],'member'=>$etcGroupMembershipFields[1]);
        }

        function groupForGroupMemberShip($indiestorGroupMembership)
        {
		$splitFields=$this->splitGroupMemberShip($indiestorGroupMembership);
                return $splitFields['group'];
        }

        function memberForGroupMemberShip($indiestorGroupMembership)
        {
		$splitFields=$this->splitGroupMemberShip($indiestorGroupMembership);
                return $splitFields['member'];
        }

        function groupsForUser($user)
        {
		if(!EtcPasswd::instance()->exists($user)) return array();
                $groupsForUser=shell_exec("id -nG $user");
                if($groupsForUser==null) return array();
                $groups=explode(' ',$groupsForUser);
                $result=array();
                foreach($groups as $group)
                {
                        $result[$group]=$group;
                }
                return $result;
        }

        function removeGroupFromGroups($groups,$groupToRemove)
        {
                $result=array();
                foreach($groups as $group)
                {
                        if($group!=$groupToRemove)
                                $result[$group]=$group;
                }
                return $result;
        }

	function deleteElement($indiestorGroupMembership)
	{
                $group=$this->groupForGroupMemberShip($indiestorGroupMembership);
                $member=$this->memberForGroupMemberShip($indiestorGroupMembership);
                $groupsForUser=$this->groupsForUser($member);
                $newGroupsForUser=$this->removeGroupFromGroups($groupsForUser,$group); 
                $groups=implode(',',$newGroupsForUser);

		if(EtcGroup::instance()->isMember($group,$member))
		{
			Shell::exec("usermod $member -G $groups");
		}
	}

	function repairElement($indiestorGroupMembership)
	{
                $group=$this->groupForGroupMemberShip($indiestorGroupMembership);
                $member=$this->memberForGroupMemberShip($indiestorGroupMembership);

		if(!EtcGroup::instance()->isMember($group,$member))
		{
        		Shell::exec("usermod -a -G $group $member");
		}
	}
}

