#!/bin/sh

# builds the debian package

./package-clean.sh
builddir=debian

mkdir $builddir
cp -R debian-files/* $builddir

fakeroot -- dpkg-buildpackage -F -I.git

