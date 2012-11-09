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

cd /home/packages/packages.indiestor.com/html/apt/ubuntu
reprepro -Vb . include precise incoming/indiestor_${version}_amd64.changes
rm incoming/indiestor_$version*

