<?php
/*
        Indiestor program
        Concept, requirements, specifications, and unit testing
        By Alex Gardiner, alex@indiestor.com
        Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        Licensed under the GPL
*/

/*

Changes a user's shell. Example:

$ chsh --shell /bin/false john

*/

function syscommand_chsh($userName,$shell)
{
	ShellCommand::exec_fail_if_error("chsh --shell $shell $userName");
}

