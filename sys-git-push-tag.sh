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

#replace version in default config:
cat config-default.sh | sed 's/package_version=.*/package_version='$version'/' > config.tmp
mv config.tmp config-default.sh
chmod a+x config-default.sh

#update git
git add -A .
git commit -a -m "$version"
git tag -a "$version" -m "$version"
git push origin master --tags

#show
echo '-----------------'
echo 'config-default.sh'
echo '-----------------'
cat config-default.sh

