#!/usr/bin/env bash
#------------------------------------------------------------
# Indiestor program
# Concept, requirements, specifications, and unit testing
# By Alex Gardiner, alex@indiestor.com
# Written by Erik Poupaert, erik@sankuru.biz
# Commissioned at peopleperhour.com 
# Licensed under the GPL
#------------------------------------------------------------
# called by the debian rules file (which is a make file)
# populates the installer with the package files
#------------------------------------------------------------

# load the default environment
source ./build.conf

builddir=debian
deployroot=$builddir/$package/usr
mkdir -p $deployroot
#-------------
#bin
#-------------
bin=$deployroot/bin
mkdir -p $bin
cp bin/* $bin
#-------------
#share
#-------------
share=$deployroot/share/$package
mkdir -p $share
cp -R lib/* $share
#-------------
#prg
#-------------
prg=$share/prg
mkdir -p $prg
cp indiestor.php $prg
cp indiestor-inotify.php $prg
#-------------
#man
#-------------
man=$deployroot/share/man/man8
mkdir -p $man
cat man/manual.txt | gzip -c > $man/indiestor.8.gz
#-------------
#etc
#-------------
etc=$builddir/$package/etc
mkdir -p $etc
cp -R etc/* $etc

