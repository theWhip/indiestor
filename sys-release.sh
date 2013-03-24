#!/usr/bin/env bash
#------------------------------------------------------------
# Indiestor program
# Concept, requirements, specifications, and unit testing
# By Alex Gardiner, alex@indiestor.com
# Written by Erik Poupaert, erik@sankuru.biz
# Commissioned at peopleperhour.com 
# Licensed under the GPL
#------------------------------------------------------------
# Copies the packages to the deployment server
#------------------------------------------------------------

package_version="$1"
scriptName=$(basename "$0")

if [ "$package_version" = "" ] ; then
        echo "Usage: $scriptName [version]"
        exit 1
fi

echo "$package_version" > VERSION.txt
./sys-git-push-commit.sh "$package_version"
./sys-git-push-tag.sh "$package_version"
./sys-git-push-tag.sh "$package_version"
./sys-package-build.sh "$package_version"
./sys-package-publish.sh "$package_version"

