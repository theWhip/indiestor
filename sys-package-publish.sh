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

# load the default environment
source ./config-default.sh

echo "copying package files for $package $package_version"
scp ../$package_$version* $user_machine:$user_repository_root/$distribution/incoming

echo "publishing files for $package_$package_version"

#set template variables in remote script
script_name_in=./sys-package-publish.remote.sh
script_name_out=/sys-package-publish.remote.distrib.sh

cat $script_name_in | sed -e 's/=package_version=/'$package_version'/g' \
                                -e 's/=distrib_version=/'$distrib_version'/g' \
                                -e 's/=architecture=/'$architecture'/g' \
                                -e 's/=distribution=/'$distribution'/g' \
        > /tmp/$script_name_out

ssh $user_machine rm -f $user_home_remote/$script_name_out 
scp /tmp/$script_name_out $user_machine:$user_home_remote/$script_name_out
ssh $user_machine chmod a+x $user_home_remote/$script_name_out
rm -f /tmp/$script_name_out

# execute remote script
ssh $user_machine $user_home_remote/$script_name_out 

