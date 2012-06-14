<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/
class FSPath
{
	var $type='';
	var $path='';
	var $linkedToPath='';
	var $owner='';
	var $group='';
	var $permissions='';

        function copy()
        {
                //better do it manually, because the 
                //built-in clone function causes endless trouble
                $copy=new FSPath();
                $copy->type=$this->type;
                $copy->path=$this->path;
                $copy->linkedToPath=$this->linkedToPath;
                $copy->owner=$this->owner;
                $copy->group=$this->group;
                $copy->permissions=$this->permissions;
                return $copy; 
        }

	function __formatField($name,$value)
	{
		if($name=="" || $value=="") return '';
		else return str_pad($name,20). ': ' . $value."\n";
	}

	function __toString()
	{
                $string='';

		$string.=$this->path;

                if($this->linkedToPath!='')
                {
                        $string.=' FROM '.$this->linkedToPath;
                }

                $string=str_pad($string,95);

		$string.=' '.str_pad($this->owner.'.'.$this->group,15).' ';
		$string.=$this->permissions;
		$string.=' ('.$this->type.')';

		return $string."\n";
	}
}

