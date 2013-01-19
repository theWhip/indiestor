#!/usr/bin/env bash
#------------------------------------------------------------
# Indiestor program
# Concept, requirements, specifications, and unit testing
# By Alex Gardiner, alex@indiestor.com
# Written by Erik Poupaert, erik@sankuru.biz
# Commissioned at peopleperhour.com 
# Licensed under the GPL
#------------------------------------------------------------
# builds the debian package
# -----------------------------------------------------------

# load the default environment
source ./config-default.sh

# remove existing packages
./sys-package-clean.sh
builddir=debian

# create build folder
mkdir $builddir
cp -R debian-files/* $builddir

#set template variables in changelog
cat $builddir/changelog | sed -e 's/=package_version=/'$package_version'/g' \
                                -e 's/=distrib_version=/'$distrib_version'/g' \
                                -e 's/=package=/'$package'/g' \
        > /tmp/changelog.tmp
mv /tmp/changelog.tmp $builddir/changelog
rm -f /tmp/changelog.tmp

# execute the build
fakeroot -- dpkg-buildpackage -a$architecture -F -I.git

