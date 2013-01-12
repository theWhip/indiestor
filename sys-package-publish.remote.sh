#!/usr/bin/env bash
#------------------------------------------------------------
#        Indiestor program
#
#	 Written by Erik Poupaert, erik@sankuru.biz
#        Commissioned at peopleperhour.com 
#        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
#------------------------------------------------------------
# Runs on the remote deployment server
# Executes the reprepro script to populate the deployment
# repository
#------------------------------------------------------------

cd /home/packages/packages.indiestor.com/html/apt/=distribution=
reprepro -Vb . include =distrib_version= incoming/indiestor_=package_version=_=architecture=.changes
rm incoming/indiestor_=package_version=*

