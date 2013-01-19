#!/usr/bin/env bash
#------------------------------------------------------------
# Indiestor program
# Concept, requirements, specifications, and unit testing
# By Alex Gardiner, alex@indiestor.com
# Written by Erik Poupaert, erik@sankuru.biz
# Commissioned at peopleperhour.com 
# Licensed under the GPL
#------------------------------------------------------------
# exchange of SSH keys with deployment server
# -----------------------------------------------------------
remoteUserAtServer="$1"

function usage()
{
	echo "USAGE:"
	echo "$0 'user@server.tld'"
}

if [ "$remoteUserAtServer" = "" ]; then
	usage
	exit
fi

#install RSA keys
if [[ -e ~/.ssh/id_rsa && -e ~/.ssh/id_rsa.pub ]]; then
	echo "OK. Local RSA keys exist."
else
	echo "Generating RSA keys"
	rm -f ~/.ssh/id_rsa*
	ssh-keygen -q -t rsa -f ~/.ssh/id_rsa -N ""
fi

#copy keys
echo "copying local key to remote server ..."
ssh-copy-id "$remoteUserAtServer"
echo "adding ssh identity locally ..."
ssh-add
echo "You can login as $remoteUserAtServer without password now."

