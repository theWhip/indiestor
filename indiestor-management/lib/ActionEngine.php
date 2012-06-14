<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('actions/EntityType.php');
require_once('args/ProgramActions.php');
require_once('etcfiles/all.php');
require_once('syscommands/all.php');
require_once('sysqueries/all.php');

define('ERRNUM_USER_EXISTS_ALREADY',50);
define('ERRNUM_GROUP_EXISTS_ALREADY',51);
define('ERRNUM_GROUP_DOES_NOT_EXISTS',52);
define('ERRNUM_USER_DOES_NOT_EXIST',53);
define('ERRNUM_GROUP_DOES_NOT_EXIST',54);
define('ERRNUM_DUPLICATE_MEMBERSHIP',55);
define('ERRNUM_USER_NOT_MEMBER_OF_ANY_GROUP',56);
define('ERRNUM_CANNOT_ADD_INDIESTOR_SYSUSER',57);
define('ERRNUM_USERNAME_INVALID_CHARACTERS',58);
define('ERRNUM_GROUPNAME_INVALID_CHARACTERS',58);
define('ERRNUM_MOVE_HOME_CONTENT_WITHOUT_SET_HOME',59);
define('ERRNUM_CANNOT_MOVE_HOME_CONTENT_TO_EXISTING_FOLDER',60);
define('ERRNUM_CANNOT_MOVE_HOME_TO_NON_FOLDER',61);
define('ERRNUM_HOME_FOLDER_MUST_BE_ABSOLUTE_PATH',62);
define('ERRNUM_REMOVE_HOME_CONTENT_WITHOUT_DELETE',63);
define('ERRNUM_HOME_FOLDER_ALREADY_BELONGS_TO_USER',64);
<<<<<<< HEAD
=======
define('ERRNUM_VOLUME_DEVICE_CANNOT_FIND_UUID',65);
define('ERRNUM_VOLUME_CANNOT_FIND_DEVICE_NOR_UUID',66);
define('ERRNUM_QUOTA_ALREADY_ON_FOR_DEVICE',67);
define('ERRNUM_QUOTA_ALREADY_OFF_FOR_DEVICE',68);
>>>>>>> added --volume -quota-remove --volumes -purge-fstab-backups

class ActionEngine
{
	const indiestorGroupPrefix='is_';
	const indiestorUserGroup='indiestor-users';
	const indiestorSysUserName='indiestor';

	static function printStdErr($msg)
	{
		file_put_contents('php://stderr',$msg);
	}

	static function error($msg,$errNum)
	{
		self::printStdErr("Error $errNum: $msg.\n");
		exit($errNum);
	}

	static function sysGroupName($indieStorGroupName)
	{
		return self::indiestorGroupPrefix.$indieStorGroupName;
	}

	static function isSysGroupIndiestorGroup($sysGroupName)
	{
		$lenISGPrefix=strlen(self::indiestorGroupPrefix);
                if(strlen($sysGroupName)>= $lenISGPrefix) 
			$prefix=substr($sysGroupName,0,$lenISGPrefix);
		else return false;
                if($prefix==self::indiestorGroupPrefix)
			return true;
		else return false;
	}

	static function isIndiestorSysUserName($userName)
	{
		return $userName==self::indiestorSysUserName;
	}

	static function indiestorGroupName($sysGroupName)
	{
		$lenISGPrefix=strlen(self::indiestorGroupPrefix);
		if(!self::isSysGroupIndiestorGroup($sysGroupName)) return '';
                return substr($sysGroupName,$lenISGPrefix);
	}

	static function isValidCharactersInName($name)
	{
		//a valid name must start with a letter
		//and be followed by a letter of a digit, a dash or an underscore
		return preg_match('/^[a-z][-a-z0-9_]*$/',$name);
	}

<<<<<<< HEAD
=======
	static function switchOnQuotaForMountPoint($mountPoint)
	{
		syscommand_touch("$mountPoint/quota.user");
		syscommand_touch("$mountPoint/quota.group");
		syscommand_chmod_numeric("$mountPoint/quota.*",'600');
		syscommand_mount_remount($mountPoint);
//XXX		syscommand_quotacheck_mountpoint($mountPoint);
		syscommand_quotaon($mountPoint);
	}

	static function switchOffQuotaForMountPoint($mountPoint)
	{
		syscommand_quotaoff($mountPoint);
	}

>>>>>>> added --volume -quota-remove --volumes -purge-fstab-backups
        static function execute()
        {
                $className=self::actionCamelCaseName(ProgramActions::$entityType);
                $scriptName='actions/'.$className.'.php';
                require_once($scriptName);
                $className::execute();
        }

        static function actionCamelCaseName($actionName)
        {
                $stringParts=explode('-',$actionName);
                $stringParts2=array();
                foreach($stringParts as $stringPart)
                {
                        $stringParts2[]=ucfirst($stringPart);
                }
                $actionCameCaseName=implode('',$stringParts2);
                return $actionCameCaseName;
        }

        static function actionCamelCaseNameWithFirstLowerCase($actionName)
        {
                $stringParts=explode('-',$actionName);
                $stringParts2=array();
                $i=0;
                foreach($stringParts as $stringPart)
                {
                        if($i==0) $stringParts2[]=strtolower($stringPart);
                        else $stringParts2[]=ucfirst($stringPart);
                        $i++;
                }
                $actionCamelCaseNameWithFirstLowerCase=implode('',$stringParts2);
                return $actionCamelCaseNameWithFirstLowerCase;
        }
}
