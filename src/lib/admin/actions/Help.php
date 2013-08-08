<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/


class Help extends EntityType
{
        static function default_action($commandAction)
        {
		global $argv;
		$argEngine=new ArgEngine($argv);
		$argEngine->usage();
        }
}

