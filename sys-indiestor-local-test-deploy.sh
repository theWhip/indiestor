#!/usr/bin/env bash
#------------------------------------------------------------
#        Indiestor program
#
#	 Written by Erik Poupaert, erik@sankuru.biz
#        Commissioned at peopleperhour.com 
#        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
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

