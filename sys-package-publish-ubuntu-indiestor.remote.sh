#!/usr/bin/env bash
#------------------------------------------------------------
#        Indiestor program
#
#	 Written by Erik Poupaert, erik@sankuru.biz
#        Commissioned at peopleperhour.com 
#        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
#------------------------------------------------------------
# Runs on the remote deployment server
# Executes the reprepro script to populate the deployment
# repository
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

cd /home/packages/packages.indiestor.com/html/apt/ubuntu
reprepro -Vb . include precise incoming/indiestor_${version}_amd64.changes
rm incoming/indiestor_$version*

