#!/usr/bin/env bash
#------------------------------------------------------------
#        Indiestor program
#
#	 Written by Erik Poupaert, erik@sankuru.biz
#        Commissioned at peopleperhour.com 
#        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
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
        > $builddir/changelog.tmp
mv $builddir/changelog.tmp $builddir/changelog
rm -f $builddir/changelog.tmp

# execute the build
fakeroot -- dpkg-buildpackage -a$architecture -F -I.git

