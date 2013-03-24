#!/usr/bin/env bash
#------------------------------------------------------------
# Indiestor program
# Concept, requirements, specifications, and unit testing
# By Alex Gardiner, alex@indiestor.com
# Written by Erik Poupaert, erik@sankuru.biz
# Commissioned at peopleperhour.com 
# Licensed under the GPL
#------------------------------------------------------------
# deletes the debian folder and the packages generated
# reverses the sys-package-build.sh command
# -----------------------------------------------------------

# load the default environment
source ./build.conf

rm -rf debian
rm -f build-stamp
rm -f ../${package}_*
