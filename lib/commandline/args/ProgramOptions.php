<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('ProgramActions.php');
require_once('lib/Shell.php');

class ProgramOptions
{
	static $simulation=false;
	static $verbose=false;
	static $debug=false;

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
                                case 'simulate': self::$simulation=true; Shell::$simulation=true; break;
                                case 'verbose': self::$verbose=true; Shell::$verbose=true; break;
                                case 'debug': self::$debug=true; break;
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
		$buffer.="debug: ".self::bool2String(self::$debug)."\n";
		return $buffer;
        }
}

