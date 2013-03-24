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

source ./build.conf

package_version="$1"
scriptName=$(basename "$0")

if [ "$package_version" = "" ] ; then
        echo "Usage: $scriptName [version]"
        exit 1
fi

# remove existing packages
./sys-package-clean.sh
builddir=debian

# create build folder
mkdir $builddir
cp -R debian-files/* $builddir

#set template variables in changelog
cat $builddir/changelog | sed -e 's/=package_version=/'$package_version'/g' \
                                -e 's/=distrib_release=/'$distrib_release'/g' \
                                -e 's/=package=/'$package'/g' \
        > /tmp/changelog.tmp
mv /tmp/changelog.tmp $builddir/changelog
rm -f /tmp/changelog.tmp

# execute the build
fakeroot -- dpkg-buildpackage -a$architecture -F -I.git

