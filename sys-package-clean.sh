#!/usr/bin/env bash
#------------------------------------------------------------
#        Indiestor program
#
#	 Written by Erik Poupaert, erik@sankuru.biz
#        Commissioned at peopleperhour.com 
#        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
#------------------------------------------------------------
# deletes the debian folder and the packages generated
# reverses the sys-package-build.sh command
# -----------------------------------------------------------
rm -rf debian
rm -f build-stamp
rm -f ../indiestor_*
