<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

//action definition folder
define('COMMAND_ARGS_DEFINITIONS_FOLDER', folderParentAtLevel(__FILE__,3).'/lib/admin/arg-definitions');

function folderParentAtLevel($file,$level)
{
	for($i=0; $i<$level; $i++)
	{
		$file=dirname($file);
	}
	return $file;
}

class DefinitionFile
{
	function parse($fileName,$columnDefinitions)
	{
		$filePath=COMMAND_ARGS_DEFINITIONS_FOLDER.'/'.$fileName.'.conf';
		$fileLines=file($filePath);
		return self::parseRows($fileLines,$columnDefinitions,$filePath);
	}

	function parseRows($lines,$columnDefinitions,$filePath)
	{
		$rows=array();
		$lineNumber=0;
		foreach($lines as $line)
		{
			$lineNumber++;
			$line=trim($line);
			if($line=='') continue; //skip empty lines
			if($line[0]=='#')continue; //skip comments
			$rows[]=self::parseLineFields($line,$columnDefinitions,$filePath,$lineNumber);
		}
		return $rows;
	}

	function parseLineFields($line,$columnDefinitions,$filePath,$lineNumber)
	{
		//count column definitions
		$countColumnDefinitions=count($columnDefinitions);
		//split according to word boundaries, with maximum the number of columns defined
		$fields=preg_split("/[\s]+/",$line,$countColumnDefinitions);
		//we must have the same field count in the line as in the columnDefinitions (not fewer)
		$countFields=count($fields);
		if($countFields!=$countColumnDefinitions)
			throw new Exception("Error parsing file '$filePath' in line $lineNumber. ".
				"Fields expected: $countColumnDefinitions. Fields counted: $countFields.");
		//match the field with the columns
		$row=array();
		for($i=0; $i<$countFields; $i++)
		{
			$columnDefinition=$columnDefinitions[$i];
			$field=$fields[$i];
			if(strtolower($field)=='yes') $field=true;
			if(strtolower($field)=='no') $field=false;
			$row[$columnDefinition]=$field;
		}
		return $row;
	}

	function fieldArray($pregFields)
	{
		$fields=array();
		foreach($pregFields as $pregField)
		{
			$fields[]=$pregField[0];
		}
		return $fields;
	}
}

