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
message="$2"

function usage()
{
	echo "USAGE:"
	echo "$0 'version' 'message'"
}

if [ "$version" = "" ]; then
	usage
	exit
fi

if [ "$message" = "" ]; then
	usage
	exit
fi

#replace version in default config:
cat config-default.sh | sed 's/package_version=.*/package_version='$version'/' > config.tmp
mv config.tmp config-default.sh
rm config.tmp
chmod a+x config-default.sh

git add -A .
git commit -a -m "$message"
git tag "$version" -m "$version"
git push --tags origin master

#show
echo '-----------------'
echo 'config-default.sh'
echo '-----------------'
cat config-default.sh

