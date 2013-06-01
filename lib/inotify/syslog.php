<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

function syslog_notice($msg)
{
	syslog(LOG_NOTICE,getmypid().' '.$msg);
}

function syslog_notice_start_running()
{
	syslog_notice("-------------------------------------------");
	syslog_notice("---------START NEW INDIESTOR EVENT---------");
	syslog_notice("-------------------------------------------");
}

function syslog_notice_end_running()
{
	syslog_notice("-------------------------------------------");
	syslog_notice("------------END INDIESTOR EVENT------------");
	syslog_notice("-------------------------------------------");
}

function terminate($msg)
{
	syslog_notice($msg);
	syslog_notice_end_running();
	exit(1);
}

