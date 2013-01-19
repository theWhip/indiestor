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

cd /home/packages/packages.indiestor.com/html/apt/ubuntu
reprepro -Vb . include precise incoming/indiestor_0.8.0.10_amd64.changes
rm incoming/indiestor_0.8.0.10*

