#!/bin/bash

wget -q -O - http://packages.indiestor.com/indiestor.gpg.key |  sudo apt-key add -
aptlist=/etc/apt/sources.list.d/indiestor.com.list
sudo rm -f $aptlist
echo "deb http://packages.indiestor.com/apt/ubuntu precise main" | sudo tee $aptlist
echo "deb-src http://packages.indiestor.com/apt/ubuntu precise main" | sudo tee -a $aptlist
sudo apt-get update
sudo apt-get install indiestor

