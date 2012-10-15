#!/bin/sh

#rapid local deployment for testing purposes

cp indiestor /usr/bin
cp indiestor-inotify /usr/bin
if [ ! -d /usr/share/indiestor ]; then
	mkdir /usr/share/indiestor
fi
cp -r lib/* /usr/share/indiestor

