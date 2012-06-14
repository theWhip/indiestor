<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

/*

Disables quota. Example:

$ quotaoff /

*/

function syscommand_quotaoff($mountPoint)
{
	ShellCommand::exec("quotaoff -u $mountPoint");
}

