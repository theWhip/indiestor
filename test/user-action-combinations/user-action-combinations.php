#!/usr/bin/php
<?php

//the 'expel' action is always valid

$actions=array(
	 'add'
	,'delete'
	,'set-home'
	,'remove-home'
	,'move-home-content'
	,'set-group'
//	,'unset-group'
	,'set-quota'
//	,'remove-quota'
	,'set-passwd'
	,'lock'
	,'remove-from-indiestor'
	,'show'
);

$forbidden=array(
	array('add','delete'),
	array('add','remove-home'), 
	array('add','move-home-content'), 
	array('add','unset-group'), 
	array('delete','set-home'),
	array('delete','move-home-content'),
	array('delete','set-group'),
	array('delete','unset-group'),
	array('delete','set-quota'),
	array('delete','remove-quota'),
	array('delete','set-passwd'),
	array('delete','lock'),
	array('set-home','remove-home'),
	array('set-group','unset-group'),
	array('set-quota','remove-quota'),
	array('set-passwd','lock'),
	array('remove-from-indiestor','add'),
	array('move-home-content','remove-home')
);

$mandatory=array(
	array('remove-home','delete'),
	array('move-home-content','set-home')
);

$singletons=array('remove-from-indiestor','show');

$countActions=count($actions); //number of actions
$n=pow(2,$countActions); //number of combinations

$i=0;
$allowed=0;
for($i=0; $i<$n; $i++)
{
	$binary=dec2bin($i);
	$combinationArray=combinationArrayForNumber($binary);
	if(count($combinationArray)>1)
	{
		if(is_forbidden($combinationArray)) continue;
		if(fails_mandatory($combinationArray)) continue;
		if(fails_singleton_requirement($combinationArray)) continue;
		//ok, all requirements are met
		$allowed++;
		$actionArrayString=ActionArrayString($combinationArray);
		printf("%6d %6d %s %s\n",$i,$allowed,$binary,$actionArrayString);
	}
}

function fails_singleton_requirement($combinationArray)
{
	global $singletons;
	foreach($singletons as $singleton)
	{
		if(array_key_exists($singleton,$combinationArray)) return true;
	}
	return false;
}

function fails_mandatory($combinationArray)
{
	global $mandatory;
	foreach($mandatory as $mandatoryTuple)
	{
		$action1=$mandatoryTuple[0];
		$action2=$mandatoryTuple[1];
		if(array_key_exists($action1,$combinationArray) && 
			!array_key_exists($action2,$combinationArray)) 
				return true;
	}
	return false;
}

function is_forbidden($combinationArray)
{
	global $forbidden;
	foreach($forbidden as $forbiddenTuple)
	{
		$action1=$forbiddenTuple[0];
		$action2=$forbiddenTuple[1];
		if(array_key_exists($action1,$combinationArray) && 
			array_key_exists($action2,$combinationArray)) 
				return true;
	}
	return false;
}

function combinationArrayForNumber($binary)
{
	global $actions;
	$i=0;
	$combinationArray=array();
	foreach($actions as $action)
	{
		$bit=$binary[$i];
		$action=$actions[$i];
		if($bit==="1") $combinationArray[$action]=$action;
		$i++;
	}
	return $combinationArray;
}

function ActionArrayString($combinationArray)
{
	global $actions;
	$buffer='(';
	$i=0;
	foreach($combinationArray as $action)
	{
		if($i>0) $buffer.=',';
		$buffer.=$action; 
		$i++;
	}
	$buffer.=')';
	return $buffer;
}

function dec2bin($dec)
{
	global $countActions;
	for($b='',$r=$dec;$r>1;)
	{
        	$n=floor($r/2);
		$b=($r-$n*2).$b;
		$r=$n; // $r%2 is inaccurate when using bigger values (like 11.435.168.214)!
	}
    	$bin=($r%2).$b;
	return str_pad($bin,$countActions,'0',STR_PAD_LEFT);
}

