<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class Groups extends EntityType
{

	static function noMembers()
	{
<<<<<<< HEAD
		echo "no indiestor members\n";
=======
		echo "no indiestor groups\n";
>>>>>>> fixed bug in validating indiestor group membership
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
}
