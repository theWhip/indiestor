<?php

/*
        Indiestor simulation program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class JSONFile
{
	//-----------------------------------------------
	// LOAD DEFINITION FILE
	//-----------------------------------------------

        function load($filePath)
        {
		$json=file_get_contents($filePath);
		if(trim($json)=='[]') return array();
                $data=self::json_decode_nice($json);
                if($data==NULL)
                {
                        echo "ERROR while loading $filePath\n";
		        die(
			        'JSON ERROR:'.self::json_last_error_description()."\n".
			        "use http://jsonlint.com to validate the json data\n"
		        );
                }
                return $data;
        }

	//-----------------------------------------------
	// DECODE NICE
	//-----------------------------------------------

	function json_decode_nice($json, $assoc = FALSE)
	{
		//check if php5 has json support enabled
		if(!function_exists('json_decode'))
		{
			die("ERROR: PHP/JSON not installed on this system\n");
		}

		//replace newlines
		$json = str_replace(array("\n","\r"),"",$json);

		//another json fix
		$json = preg_replace('/([{,])(\s*)([^"]+?)\s*:/','$1"$3":',$json);

		return json_decode($json,$assoc);
	}

	//-----------------------------------------------
	// LAST ERROR DESCRIPTION
	//-----------------------------------------------

	function json_last_error_description()
	{
		return self::json_error_description_for_code(json_last_error());
	}

	//-----------------------------------------------
	// ERROR DESCRIPTION FOR CODE
	//-----------------------------------------------

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
}

