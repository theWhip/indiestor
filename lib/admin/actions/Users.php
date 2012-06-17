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
}

