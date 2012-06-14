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
	static $groupsFilePath=null;
	static $previousGroupsFilePath=null;
	static $simulation=false;
	static $verbose=false;

        static function ifNull($defaultValue,$value)
        {
                if($value==null) return dirname(__FILE__).'/'.$defaultValue;
                else return $value;
        }

        static function groupsFilePath()
        {
                return self::ifNull('groups.json',self::$groupsFilePath);
        }

        static function previousGroupsFilePath()
        {
                return self::ifNull('groups.previous.json',self::$previousGroupsFilePath);
        }

        static function extractFromProgramActions()
        {
                $programOptions=ProgramActions::extractProgramOptions();
                foreach($programOptions as $programOption)
                {
                        switch($programOption->action)
                        {
                                case 'configfile': self::$groupsFilePath=$programOption->actionArg; break;
                                case 'previous-configfile': self::$previousGroupsFilePath=$programOption->actionArg; break;
                                case 'simulate': self::$simulation=true; Shell::$simulation=true; break;
                                case 'verbose': self::$verbose=true; Shell::$verbose=true; break;
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
		$buffer.="groups file: ".self::groupsFilePath()."\n";
		$buffer.="previous groups file: ".self::previousGroupsFilePath()."\n";
		$buffer.="simulation: ".self::bool2String(self::$simulation)."\n";
		$buffer.="verbose: ".self::bool2String(self::$verbose)."\n";
		return $buffer;
        }
}

