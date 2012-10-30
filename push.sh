#!/bin/bash

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

git add -A
git commit -a -m "$message"
git tag "$version" -m "$version"
git push --tags origin master

