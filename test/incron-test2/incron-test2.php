#!/usr/bin/php
<?php
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$outputFile=dirname(__FILE__).'/incron.log';
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($outputFile,'a');
$STDERR = fopen($outputFile,'a');
ini_set('display_errors','On');
ini_set($outputFile,$outputFile);

$date=date(DATE_RFC822);
$watchType=$argv[1];
$folderWatched=$argv[2];
$fsObject=$argv[3];
$events=$argv[4];

require_once('InotifyEventHandler.php');

$inotifyEventHandler=new InotifyEventHandler(dirname(__FILE__).'/in_move_pending');

//process number
$pid=getmypid();

echo "pid=$pid type=$watchType object='$fsObject' events=$events\n";

try
{
	$decision=$inotifyEventHandler->decision($watchType,$folderWatched,$fsObject,$events);
}
catch(Exception $e)
{
	$decision='ERROR:'.$e->getMessage();
}

if($decision instanceof RenameOperation)
{
	$from=$decision->from;
	$to=$decision->to;
	$decision="RENAME '$from' TO '$to'";
}

echo "====>pid=$pid decision=$decision\n";

