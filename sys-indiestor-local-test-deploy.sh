#!/usr/bin/env bash
#------------------------------------------------------------
# Indiestor program
# Concept, requirements, specifications, and unit testing
# By Alex Gardiner, alex@indiestor.com
# Written by Erik Poupaert, erik@sankuru.biz
# Commissioned at peopleperhour.com 
# Licensed under the GPL
#------------------------------------------------------------
#rapid local deployment for testing purposes
# -----------------------------------------------------------
cp bin/* /usr/bin
if [ ! -d /usr/share/indiestor ]; then
	mkdir /usr/share/indiestor
	mkdir /usr/share/indiestor/prg
fi
cp -r lib/* /usr/share/indiestor
cp indiestor /usr/share/indiestor/prg
cp indiestor-inotify /usr/share/indiestor/prg

