#!/usr/bin/env bash
#------------------------------------------------------------
#        Indiestor program
#
#	 Written by Erik Poupaert, erik@sankuru.biz
#        Commissioned at peopleperhour.com 
#        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
#------------------------------------------------------------
# Copies the packages to the deployment server
#------------------------------------------------------------
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

echo "copying package files for indiestor $version"
scp ../indiestor_$version* packages@packages.indiestor.com:/home/packages/packages.indiestor.com/html/apt/ubuntu/incoming
echo "publishing files for indiestor_$version"
ssh packages@packages.indiestor.com /home/packages/sys-package-publish-ubuntu-indiestor.sh $version

