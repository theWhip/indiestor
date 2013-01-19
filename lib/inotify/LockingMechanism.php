<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

class LockingMechanism
{
	var $groupName=null;

	function __construct($groupName)
	{
		$this->groupName=$groupName;
		if(!file_exists($this->lockFolder()))
			mkdir($this->lockFolder());
		$this->init();
	}

	function lockFolder()
	{
		return "/var/lock/indiestor";
	}

	function filePrefix()
	{
		return "{$this->lockFolder()}/{$this->groupName}";
	}

	function pidFile()
	{
		return "{$this->filePrefix()}.pid";
	}

	function reRunFile()
	{
		return "{$this->filePrefix()}.rerun";
	}

	function removeReRun()
	{
		if(file_exists($this->reRunFile()))
			unlink($this->reRunFile());
	}

	function lock()
	{
		if(!file_exists($this->pidFile()))
			file_put_contents($this->pidFile(),getmypid());
	}

	function unlock()
	{
		if(file_exists($this->pidFile()))
			unlink($this->pidFile());
	}

	function requestReRun()
	{
		if(!file_exists($this->reRunFile()))
		{
			syslog_notice("no existing rerun request; requesting rerun by touching file '{$this->reRunFile()}'");
			touch($this->reRunFile());
		}
		else
		{
			syslog_notice("rerun request '{$this->reRunFile()}' exists already; no need to request rerun");
		}
	}

	function init()
	{
		if($this->isBusy())
		{
			$this->requestReRun();
			terminate("rerun scheduled; terminating");
		}

		$this->removeReRun();
	}

	function isBusy()
	{
		if(file_exists($this->pidFile()))
		{
			syslog_notice("locking pid file '{$this->pidFile()}' exists");
			$pid=file_get_contents($this->pidFile());
			if(file_exists("/proc/$pid"))
			{
				syslog_notice("previous process '/proc/$pid' still active; not free to run");
				return true;
			}
			else
			{
				syslog_notice("previous process '/proc/$pid' no longer active; free to run");
				unlink($this->pidFile());
				return false;
			}
		}
		else
		{
			syslog_notice("no locking pid file '{$this->pidFile()}'; free to run");
			return false;
		}
	}

	function mustReRun()
	{
		if(file_exists($this->reRunFile()))
		{
			syslog_notice("re-run file '{$this->reRunFile()}' present; re-run required");
			$this->RemoveReRun();
			return true;
		}
		else
		{
			syslog_notice("no re-run file '{$this->reRunFile()}' present; terminating");
			return false;
		}
	}
}

