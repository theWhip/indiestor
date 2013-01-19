#!/usr/bin/env bash
#------------------------------------------------------------
# Indiestor program
# Concept, requirements, specifications, and unit testing
# By Alex Gardiner, alex@indiestor.com
# Written by Erik Poupaert, erik@sankuru.biz
# Commissioned at peopleperhour.com 
# Licensed under the GPL
#------------------------------------------------------------
# Runs on the remote deployment server
# Executes the reprepro script to populate the deployment
# repository
#------------------------------------------------------------

cd /home/packages/packages.indiestor.com/html/apt/=distribution=
reprepro -Vb . include =distrib_version= incoming/indiestor_=package_version=_=architecture=.changes
rm incoming/indiestor_=package_version=*

