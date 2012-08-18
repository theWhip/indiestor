<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/


class Users extends EntityType
{
        static function show($commandAction)
        {
                $etcGroup=EtcGroup::instance();
		$indiestorGroup=$etcGroup->indiestorGroup;
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
}

