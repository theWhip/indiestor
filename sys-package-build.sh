#!/bin/sh

# builds the debian package

./sys-package-clean.sh
builddir=debian

mkdir $builddir
cp -R debian-files/* $builddir

fakeroot -- dpkg-buildpackage -aamd64 -F -I.git
#debuild -S

