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

source ./build.conf

package_version="$1"
scriptName=$(basename "$0")

if [ "$package_version" = "" ] ; then
        echo "Usage: $scriptName [version]"
        exit 1
fi

echo "copying package files for $package $package_version"
cd ..
files=$(find . -maxdepth 1 -type f)
scp $files $user_machine:$user_repository_root/$distribution/incoming

echo "publishing files for ${package} ${package_version}"
remote_command="
cd /home/packages/packages.indiestor.com/html/apt/${distribution}
reprepro -Vb . include ${distrib_release} incoming/indiestor_${package_version}_${architecture}.changes
rm incoming/indiestor_${package_version}*
"

# execute remote script
ssh $user_machine 'bash -s' <<< "$remote_command"

