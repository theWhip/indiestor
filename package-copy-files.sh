#!/bin/sh

# populates the installer with the package files

builddir=debian
deployroot=$builddir/indiestor/usr
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
share=$deployroot/share/indiestor
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
etc=$builddir/indiestor/etc
mkdir -p $etc
cp -R etc/* $etc

