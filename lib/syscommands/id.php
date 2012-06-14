<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

require_once('query.php');

function syscommand_id_nG($userName)
{
	return syscommand_query("id -nG $userName");
}

