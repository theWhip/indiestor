#!/usr/bin/env bash
#------------------------------------------------------------
# Indiestor program
# Concept, requirements, specifications, and unit testing
# By Alex Gardiner, alex@indiestor.com
# Written by Erik Poupaert, erik@sankuru.biz
# Commissioned at peopleperhour.com 
# Licensed under the GPL
#------------------------------------------------------------
# pushes a version tag to git repository
# -----------------------------------------------------------
version="$1"

function usage()
{
	echo "USAGE:"
	echo "$0 'version'"
}

if [ "$version" = "" ]; then
	usage
	exit
fi

#update git
git tag -a "$version" -m "$version"
git push origin master --tags

