<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/


class Help extends EntityType
{
        static function default_action($commandAction)
        {
		global $argv;
<<<<<<< HEAD
		$commandLineArgs=new CommandLineArgs($argv);
		$commandLineArgs->usage();
=======
		$argEngine=new ArgEngine($argv);
		$argEngine->usage();
>>>>>>> added --user -expel and validation fixes
        }
}

