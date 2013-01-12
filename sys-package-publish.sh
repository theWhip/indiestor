#!/bin/bash

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
ssh packages@packages.indiestor.com /home/packages/package-publish-ubuntu-indiestor.sh $version

