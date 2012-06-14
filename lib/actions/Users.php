<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/


class Users extends EntityType
{
	static function noMembers()
	{
		echo "no indiestor users\n";
	}

        static function show($commandAction)
        {
                $etcGroup=EtcGroup::instance();
		$indiestorGroup=$etcGroup->indiestorGroup;

		if($indiestorGroup==null) 
		{
			self::noMembers();
			return;
		}

		if(count($indiestorGroup->members)==0) 
		{
			self::noMembers();
			return;
		}

		$userRecords=array();
                foreach($indiestorGroup->members as $member)
                {
			$userRecord=new UserReportRecord($member);
			$userRecords[]=$userRecord;
                }

		ActionEngine::printUserRecords($userRecords);
        }
}

