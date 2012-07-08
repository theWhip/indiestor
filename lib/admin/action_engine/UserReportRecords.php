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

		$sambaUsers=sysquery_pdbedit_list();

		$format1="%-10s %-20s %-6s %-10s %5s %5s %5s %-5s %-5s\n";
		$format2="%-10s %-20s %-6s %-10s %5s %5s %5s %-5s %-5s\n";
		printf($format1,'user','home','locked','group','quota','used','%used','samba','flags');
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

			if(array_key_exists($userReportRecord->userName,$sambaUsers))
			{
				$sambaUser=$sambaUsers[$userReportRecord->userName];
				$samba='Y';
				$flags=implode('',$sambaUser['sambaFlagArray']);
			}
			else
			{
				$samba='N';
				$flags='-';
			}

			printf($format2,
				$userReportRecord->userName,
				$userReportRecord->homeFolder,
				$locked,
				$groupName,
				$quotaTotalGB,
				$quotaUsedGB,
				$quotaUsedPerc,
				$samba,
				$flags);  
		}
	}
}

