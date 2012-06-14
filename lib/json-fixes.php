<?php
/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/
function json_decode_nice($jsonfile, $assoc = FALSE)
{
	if(!function_exists('json_decode'))
	{
		die("ERROR: PHP/JSON not installed on this system\n");
	}
    $json = str_replace(array("\n","\r"),"",file_get_contents($jsonfile));
    $json = preg_replace('/([{,])(\s*)([^"]+?)\s*:/','$1"$3":',$json);
    return json_decode($json,$assoc);
}

function json_last_error_description()
{
	return json_error_description_for_code(json_last_error());
}

function json_error_description_for_code($json_error_code)
{
	$constants = get_defined_constants(true);
	foreach ($constants["json"] as $name => $value)
	{
		if (!strncmp($name, "JSON_ERROR_", 11))
		{
			if($value==$json_error_code) return $name;
		}
	}
	return "unknown error";
}

