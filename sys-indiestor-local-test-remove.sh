#!/usr/bin/env bash
#------------------------------------------------------------
# Indiestor program
# Concept, requirements, specifications, and unit testing
# By Alex Gardiner, alex@indiestor.com
# Written by Erik Poupaert, erik@sankuru.biz
# Commissioned at peopleperhour.com 
# Licensed under the GPL
#------------------------------------------------------------
# removes rapid local deployment (for testing purposes)
# reverses the sys-indiestor-local-test-deploy.sh command
# -----------------------------------------------------------
rm -f /usr/bin/indiestor
rm -f /usr/bin/indiestor-inotify
rm -rf /usr/share/indiestor


