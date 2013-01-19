<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
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
		$sambaConnectedUsers=sysquery_smbstatus_processes();

		$format1="%-10s %-20s %-6s %-10s %5s %5s %5s %-5s %-5s %-5s\n";
		$format2="%-10s %-20s %-6s %-10s %5s %5s %5s %-5s %-5s %-5s\n";
		printf($format1,'user','home','locked','group','quota','used','%used','samba','flags','conn.');
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
				$quotaTotalGB=floor($userReportRecord->quotaTotalGB).'G';
				$quotaUsedGB=number_format($userReportRecord->quotaUsedGB,1).'G';
				$quotaUsedPerc=floor($userReportRecord->quotaUsedPerc).'%';
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

			if(array_key_exists($userReportRecord->userName,$sambaConnectedUsers))
			{
				$sambaConnected='Y';
			}
			else
			{
				$sambaConnected='N';
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
				$flags,
				$sambaConnected);  
		}
	}
}

