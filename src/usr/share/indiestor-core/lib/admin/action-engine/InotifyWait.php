<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

class InotifyWait
{
	static function execBackground($cmd)
	{
		exec("$cmd >/dev/null 2>&1 &");
	}

	static function stopWatchingAll()
	{
                $etcGroup=EtcGroup::instance();
                foreach($etcGroup->groups as $group)
			self::stopWatching($group->name);
	}

	static function startWatchingAll()
	{
                $etcGroup=EtcGroup::instance();
                foreach($etcGroup->groups as $group)
			self::startWatching($group->name);
	}

	static function watchProcesses($groupName)
	{
		return array_merge(
			self::watchProcessesForWatchType($groupName,'main'),
			self::watchProcessesForWatchType($groupName,'avp')
		);
	}

	static function watchProcessesForWatchType($groupName,$watchType)
	{
		$pidsPrg=sysquery_pgrep("^/bin/sh /usr/bin/indiestor-watch-group $groupName $watchType");
		$pidsInotifyWait=sysquery_pgrep("^inotifywait --exclude __{$groupName}__{$watchType}__ ");
		return array_merge($pidsPrg,$pidsInotifyWait);
	}

	static function stopWatching($groupName)
	{
		$pids=self::watchProcesses($groupName);
		foreach($pids as $pid)
			posix_kill($pid,SIGTERM);
	}

	static function startWatching($groupName)
	{
		self::stopWatching($groupName);

		$group=EtcGroup::instance()->findGroup($groupName);
		if(count($group->members)<2) return;

		$foldersMain=InotifyWatchFolders::watchesMain($group);
		if(count($foldersMain)>0)
			self::execBackground("indiestor-watch-group $groupName main");

		$foldersAVP=InotifyWatchFolders::watchesAVP($group);
		if(count($foldersAVP)>0)
			self::execBackground("indiestor-watch-group $groupName avp");
	}
}

