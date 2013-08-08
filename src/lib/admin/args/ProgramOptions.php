<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

requireLibFile('admin/args/ProgramActions.php');

class ProgramOptions
{
	static $simulation=false;
	static $verbose=false;

        static function ifNull($defaultValue,$value)
        {
                if($value==null) return dirname(__FILE__).'/'.$defaultValue;
                else return $value;
        }

        static function extractFromProgramActions()
        {
                $programOptions=ProgramActions::extractProgramOptions();
                foreach($programOptions as $programOption)
                {
                        switch($programOption->action)
                        {
                                case 'simulate': self::$simulation=true; break;
                                case 'verbose': self::$verbose=true; break;
                        }
                }
        }

	static function bool2String($bool)
	{
		if($bool) return 'true'; else return 'false';
	}

        static function toString()
        {
		$buffer="-----------------------\n";
		$buffer.="--- Program options ---\n";
		$buffer.="-----------------------\n";
		$buffer.="simulation: ".self::bool2String(self::$simulation)."\n";
		$buffer.="verbose: ".self::bool2String(self::$verbose)."\n";
		return $buffer;
        }
}

