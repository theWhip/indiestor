#!/usr/bin/env bash
#------------------------------------------------------------
# Indiestor program
# Concept, requirements, specifications, and unit testing
# By Alex Gardiner, alex@indiestor.com
# Written by Erik Poupaert, erik@sankuru.biz
# Commissioned at peopleperhour.com 
# Licensed under the GPL
#------------------------------------------------------------
# Fixes the reprepro repositories given a list of
# distributions and distrib versions to distribute
# packages for
#------------------------------------------------------------

# load the default environment
source ./config-machine.sh

#create situation files at the distrib/version level
distrib_versions_existing=/tmp/distrib_versions_existing.txt
distrib_versions_desired=/tmp/distrib_versions_desired.txt
echo "checking existing repositories on $user_machine in $user_repository_root"
ssh -n $user_machine find $user_repository_root -maxdepth 3 | grep override | \
                sed 's_.*/\(.*\)/conf/override.\(.*\)_\1/\2_' | sed '/^$/d' | sort > $distrib_versions_existing
cat sys-reprepro-list.txt | sed '/^$/d' | sort > $distrib_versions_desired

# create situation files
distribs_existing=/tmp/distribs_existing.txt
distribs_desired=/tmp/distribs_desired.txt
cat $distrib_versions_existing | awk 'BEGIN{FS="/"};{ print $1 }' | sed '/^$/d' | sort | uniq > $distribs_existing
cat $distrib_versions_desired | awk 'BEGIN{FS="/"};{ print $1 }' | sed '/^$/d' | sort | uniq > $distribs_desired

# create decision files at the distrib level
#--- DECISION FILE -- #
distribs_to_delete=/tmp/distribs_to_delete.txt 
#--- DECISION FILE -- #
distribs_to_create=/tmp/distribs_to_create.txt
join -a1 -v1 $distribs_existing $distribs_desired > $distribs_to_delete
join -a1 -v1 $distribs_desired $distribs_existing > $distribs_to_create

#create decision files at the distrib/version level
#--- DECISION FILE -- #
distribs_to_repopulate=/tmp/distribs_to_repopulate.txt
join -a1 -v1 $distrib_versions_existing $distrib_versions_desired | awk 'BEGIN{FS="/"};{ print $1 }' > $distribs_to_repopulate
join -a1 -v1 $distrib_versions_desired $distrib_versions_existing | awk 'BEGIN{FS="/"};{ print $1 }' >> $distribs_to_repopulate

#only do the repopulation once
cat $distribs_to_repopulate | sort | uniq > $distribs_to_repopulate.tmp
mv $distribs_to_repopulate.tmp $distribs_to_repopulate
rm -f $distribs_to_repopulate.tmp

#distribs -> delete
while read distrib; do
        ssh -n $user_machine rm -rf $user_repository_root/$distrib
done < $distribs_to_delete

#distribs -> create
while read distrib; do
        ssh -n $user_machine mkdir -p $user_repository_root/$distrib/{config,incoming}
done < $distribs_to_create

#distribs -> repopulate
while read distrib; do
        rm -rf /tmp/$distrib
        mkdir /tmp/$distrib

        #options file
        cat reprepro-templates/options | sed -e 's#=user_repository_root=#'$user_repository_root'#g' \
                                       -e 's/=distribution=/'$distrib'/g' \
                > /tmp/$distrib/options

        #override file and distributions file
        rm -f /tmp/$distrib/distributions
        while read distrib_version; do
                #override file
                cat reprepro-templates/override | sed -e 's/=package=/'$package'/g' \
                > /tmp/$distrib/override.$distrib_version
                #distributions file
                cat reprepro-templates/distributions \
                        | sed -e 's/=domain=/'$domain'/g' \
                        | sed -e 's/=package=/'$package'/g' \
                        | sed -e 's/=distrib_version=/'$distrib_version'/g' \
                >> /tmp/$distrib/distributions
                #add blank line to distributions file
               echo >> /tmp/$distrib/distributions
        done < <(cat $distrib_versions_desired | grep $distrib | awk 'BEGIN{FS="/"};{ print $2 }')
 
        #remove config files
         ssh -n $user_machine rm -f $user_repository_root/$distrib/config/*
         scp /tmp/$distrib/* $user_repository_root/$distrib/config

done < $distribs_to_repopulate


