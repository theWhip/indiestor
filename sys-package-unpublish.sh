#!/usr/bin/env bash
#------------------------------------------------------------
#        Indiestor program
#
#	 Written by Erik Poupaert, erik@sankuru.biz
#        Commissioned at peopleperhour.com 
#        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
#------------------------------------------------------------
# Removes the deployment packages on the deployment server
# for a particular distribution
# Reverses sys-package-publish.sh (for all versions)
#------------------------------------------------------------
ssh packages@packages.indiestor.com rm -rf /home/packages/packages.indiestor.com/html/apt/ubuntu/{db,dists,pool}

