#!/usr/bin/php
<?php
$actions=array(
	 'add'
	,'delete'
	,'expel'
	,'set-home'
	,'remove-home'
	,'move-home-content'
	,'set-group'
	,'unset-group'
	,'set-quota'
	,'remove-quota'
	,'set-passwd'
	,'lock'
	,'remove-from-indiestor'
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
	array('delete','remove-from-indiestor'),
	array('set-home','remove-home'),
	array('set-group','unset-group'),
	array('set-group','remove-from-indiestor'),
	array('unset-group','remove-from-indiestor'),
	array('set-quota','remove-quota'),
	array('set-passwd','lock')
);

$mandatory=array(
	array('remove-home','delete'),
	array('move-home-content','set-home')
);

$i=0;
$n=2^count($actions); //number of combinations

for($i=0; $i<$n; $i++)
{
	$combinationArray=combinationArrayForNumber(dec2bin($i));
}

function combinationArrayForNumber($binary)
{
	global $actions;
	$i=0;
	echo $binary;
}

function dec2bin($dec)
{
	for($b='',$r=$dec;$r>1;)
	{
        	$n=floor($r/2);
		$b=($r-$n*2).$b;
		$r=$n; // $r%2 is inaccurate when using bigger values (like 11.435.168.214)!
	}
    	return ($r%2).$b;
}

