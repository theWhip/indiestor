#!/usr/bin/env bash
#------------------------------------------------------------
#        Indiestor program
#
#	 Written by Erik Poupaert, erik@sankuru.biz
#        Commissioned at peopleperhour.com 
#        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
#------------------------------------------------------------
# called by the debian rules file (which is a make file)
# populates the installer with the package files
#------------------------------------------------------------

# load the default environment
source ./config-default.sh

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
cp indiestor $prg
cp indiestor-inotify $prg
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

