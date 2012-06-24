<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class UserReportRecords
{
	var $records=null;

	function __construct($userNames)
	{
		$this->records=array();
		if($userNames==null) return;
                foreach($userNames as $userName)
                {
			$userRecord=new UserReportRecord($userName);
			$this->records[]=$userRecord;
                }
	}

	function output()
	{
		if(count($this->records)==0) 
		{
			echo "no users\n";
			return;
		}

		$format1="%-10s %-20s %-7s %-10s %10s %10s %10s\n";
		$format2="%-10s %-20s %-7s %-10s %10s %10s %10s\n";
		printf($format1,'user','home','locked','group','quota','used','%used');
		foreach($this->records as $userReportRecord)
		{
			//locked
			if($userReportRecord->locked) $locked='Y';
			else $locked='N';

			//groupName
			if($userReportRecord->groupName==null) $groupName='(none)';
			else $groupName=$userReportRecord->groupName;

			//quota
			if($userReportRecord->hasQuotaRecord)
			{
				$quotaTotalGB=number_format($userReportRecord->quotaTotalGB,2).'G';
				$quotaUsedGB=number_format($userReportRecord->quotaUsedGB,2).'G';
				$quotaUsedPerc=number_format($userReportRecord->quotaUsedPerc,0).'%';
			}
			else
			{
				$quotaTotalGB='-';
				$quotaUsedGB='-';
				$quotaUsedPerc='-';
			}

			printf($format2,
				$userReportRecord->userName,
				$userReportRecord->homeFolder,
				$locked,
				$groupName,
				$quotaTotalGB,
				$quotaUsedGB,
				$quotaUsedPerc);  
		}
	}
}

