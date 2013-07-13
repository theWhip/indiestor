<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

class Groups extends EntityType
{

	static function noMembers()
	{
		echo "no indiestor groups\n";
	}

        static function show($commandAction)
        {
                $etcGroup=EtcGroup::instance();

		if(count($etcGroup->groups)==0) 
		{
			self::noMembers();
			return;
		}

                foreach($etcGroup->groups as $group)
                {
                        echo "$group->name\n";
                }
        }

	static function startWatching($commandAction)
	{
		InotifyWait::startWatchingAll();
	}

	static function stopWatching($commandAction)
	{
		InotifyWait::stopWatchingAll();
	}

}

