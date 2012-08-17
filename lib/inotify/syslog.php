<?php
/*
        Indiestor inotify program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

function syslog_notice($msg)
{
	syslog(LOG_NOTICE,getmypid().' '.$msg);
}

function syslog_notice_start_running()
{
	syslog_notice("indiestor-inotify running");
}

function syslog_notice_end_running()
{
	syslog_notice('indiestor, end run');
}

function terminate($msg)
{
	syslog_notice($msg);
	syslog_notice_end_running();
	exit(1);
}

