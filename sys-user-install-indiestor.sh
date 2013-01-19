#!/usr/bin/env bash
#------------------------------------------------------------
# Indiestor program
# Concept, requirements, specifications, and unit testing
# By Alex Gardiner, alex@indiestor.com
# Written by Erik Poupaert, erik@sankuru.biz
# Commissioned at peopleperhour.com 
# Licensed under the GPL
#------------------------------------------------------------
# User script to appoint the deployment repository
# and install indiestor on his machine 
#------------------------------------------------------------

# Retrieve and install the publication key
wget -q -O - http://packages.indiestor.com/indiestor.gpg.key |  sudo apt-key add -

# Appoint indiestor debian package repository
aptlist=/etc/apt/sources.list.d/indiestor.com.list
sudo rm -f $aptlist
echo "deb http://packages.indiestor.com/apt/ubuntu precise main" | sudo tee $aptlist
echo "deb-src http://packages.indiestor.com/apt/ubuntu precise main" | sudo tee -a $aptlist

# Update overall system list of available packages
sudo apt-get update

# Install indiestor
sudo apt-get install indiestor

