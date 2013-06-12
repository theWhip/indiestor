<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/


class Users extends EntityType
{
        static function show($commandAction)
        {
                $etcGroup=EtcGroup::instance();

		//in case it's null
		$indiestorGroup=$etcGroup->indiestorGroup;
		if($indiestorGroup==null) $indiestorGroup=new stdClass;
		if(!property_exists($indiestorGroup,'members')) $indiestorGroup->members=array();

		$userReportRecords=new UserReportRecords($indiestorGroup->members);
		$userReportRecords->output();
       }

        static function showIncrontab($commandAction)
        {
		syscommand_incrontab_show();		
      	}

        static function regenerateIncrontab($commandAction)
        {
		$incrontab_old=syscommand_incrontab_list();
		Incrontab::generate();
		$incrontab_new=syscommand_incrontab_list();
		if($incrontab_old==$incrontab_new) ActionEngine::notice('AE_NOTI_INCRONTAB_NO_CHANGES');
		else ActionEngine::notice('AE_NOTI_INCRONTAB_CHANGED');
      	}

        static function reshare($commandAction)
        {
                $indiestorGroup=EtcGroup::instance()->indiestorGroup;
                if($indiestorGroup==null) 
                {
                        ActionEngine::error('AE_ERR_INDIESTOR_GROUP_DOES_NOT_EXIST');
                        return;
                }
                if($indiestorGroup->members==null) 
                {
                        ActionEngine::error('AE_ERR_INDIESTOR_GROUP_MEMBERS_DO_NOT_EXIST');
                        return;
                }
                foreach($indiestorGroup->members as $member)
                {
        		$group=EtcGroup::instance()->findGroupForUserName($member);
                        if($group===null)
                        {
                                $group=new EtcOneGroup();
                                $group->name='';
                                $group->members=array($member);
                        }
        		$members=EtcPasswd::instance()->findUsersForEtcGroup($group);
        		SharingStructureAvid::reshare($group->name,$members);
                        SharingStructureMXF::reshare($members);
                        SharingStructureDefault::reshare($group->name,$members);
                }
        }
}

