#!/usr/bin/env bash
#------------------------------------------------------------
# Indiestor program
# Concept, requirements, specifications, and unit testing
# By Alex Gardiner, alex@indiestor.com
# Written by Erik Poupaert, erik@sankuru.biz
# Commissioned at peopleperhour.com 
# Licensed under the GPL
#------------------------------------------------------------
# pushes a commit to git repository
# -----------------------------------------------------------
message="$1"

function usage()
{
	echo "USAGE:"
	echo "$0 'message'"
}

if [ "$message" = "" ]; then
	usage
	exit
fi

git add -A .
git commit -m "$message"
git pull origin master
git push origin master

