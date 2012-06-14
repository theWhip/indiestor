#!/usr/bin/php
<?php
$date=date(DATE_RFC822);
$folderWatched=$argv[1];
$events=$argv[2];
$fileInvolved=$argv[3];

logIncron("$date $folderWatched $events $fileInvolved\n");

function logIncron($msg)
{
	$logFile=fopen(dirname(__FILE__).'/incron.log','a');
	fwrite($logFile,$msg);
	fclose($logFile);
}

