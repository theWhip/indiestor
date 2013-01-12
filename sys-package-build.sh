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
./sys-package-clean.sh
builddir=debian

mkdir $builddir
cp -R debian-files/* $builddir

fakeroot -- dpkg-buildpackage -aamd64 -F -I.git
#debuild -S

