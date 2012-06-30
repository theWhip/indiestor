<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class UserReportRecord
{
	var $userName=null;
	var $homeFolder=null;
	var $device=null;
	var $locked=null;
	var $groupName=null;
	var $hasQuotaRecord=null;
	var $quotaTotalGB=null;
	var $quotaUsedGB=null;
	var $quotaUsedPerc=null;

	function __construct($userName)
	{
		$this->userName=$userName;
		$etcPasswd=EtcPasswd::instance();
		$etcUser=$etcPasswd->findUserByName($userName);
		$this->homeFolder=$etcUser->homeFolder;
		//find device for user home folder
		$this->device=sysquery_df_device_for_folder($this->homeFolder);
		$this->locked=sysquery_passwd_S_locked($userName);
		$etcGroup=EtcGroup::instance();
		$group=$etcGroup->findGroupForUserName($userName);
		if($group==null) $this->groupName=null;
		else $this->groupName=$group->name;
		$quotaRecord=sysquery_quota_u($userName);

		if($quotaRecord!=null)
		{
			$this->hasQuotaRecord=true;
			$this->quotaTotalGB=BlockGBConvertor::deviceBlocksToGB($this->device,$quotaRecord['quotaTotalBlocks']);
			$this->quotaUsedGB=BlockGBConvertor::deviceBlocksToGB($this->device,$quotaRecord['quotaUsedBlocks']);
			$this->quotaUsedPerc=$quotaRecord['quotaUsedPerc'];
		}
		else
		{
			$this->hasQuotaRecord=false;
		}
	}
}

