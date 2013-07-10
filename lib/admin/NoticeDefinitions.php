<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

class NoticeDefinition
{
	var $number=null;
	var $code=null;
	var $text=null;

	function __construct($number,$code,$text)
	{
		$this->number=$number;
		$this->code=$code;
		$this->text=$text;
	}
}

class NoticeDefinitions
{
	var $noticeDefinitions=null;

	static $instance=null;	

	static function instance()
	{
		if(self::$instance==null) self::$instance=new self();
		return self::$instance;
	}

	function __construct()
	{
		$this->noticeDefinitions=array();
		$rows=DefinitionFile::parse('noticeDefinitions',array('number','code','text'));
		foreach($rows as $row)
		{
			$this->addNoticeDefinition($row['number'],$row['code'],$row['text']);
		}
	}

	function addNoticeDefinition($number,$code,$text)
	{
		$this->noticeDefinitions[$code]=new NoticeDefinition($number,$code,$text);
	}

	function notice($messageCode,$parameters=array(),$errorStage='NOTICE')
	{
		$this->output($messageCode,$parameters,'stdout',$errorStage,'NOTICE');
	}

	function warning($messageCode,$parameters=array(),$errorStage='VALIDATION')
	{
		$this->output($messageCode,$parameters,'stderr',$errorStage,'WARNING');
	}

	function error($messageCode,$parameters=array(),$errorStage='VALIDATION')
	{
		$this->output($messageCode,$parameters,'stderr',$errorStage,'ERROR');
		exit(1);
	}

	function usageError($messageCode,$parameters=array(),$errorStage='VALIDATION')
	{
		$errNum=$this->output($messageCode,$parameters,'stderr',$errorStage,'ERROR');
		return $errNum;
	}

	function output($messageCode,$parameters=array(),$streamName,$errorStage,$errorLevel)
	{
		list($errNum,$message)=$this->resolveMessage($messageCode,$parameters,$errorStage,$errorLevel);
		$function='output'.$streamName;
		$this->$function($message);
		return $errNum;
	}

	function resolveMessage($messageCode,$parameters=array(),$errorStage,$errorLevel)
	{
		$noticeDefinition=$this->noticeDefinitions[$messageCode];
		$errNum=$noticeDefinition->number;
		$message=$this->resolveText($noticeDefinition->text,$parameters);
		return array($errNum,"$errorLevel-$errNum-$messageCode ($errorStage) $message.\n");
	}

	function resolveText($text,$parameters=array())
	{
		$patterns=array();
		$replacements=array();
		foreach($parameters as $key=>$value)
		{
			$patterns[]='/\{'.$key.'\}/';
			$replacements[]=$value;
		}
		$resolved=preg_replace($patterns,$replacements,$text);
		return $resolved;
	}

	function outputStdout($message)
	{
		echo $message;
	}

	function outputStdErr($message)
	{
		file_put_contents('php://stderr',$message);
	}
}

