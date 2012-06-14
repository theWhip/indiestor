#!/bin/bash
incrontab --remove
pwd=$(pwd)
echo $pwd
folderToWatch="$pwd/incron-folder-watched"
eventsToWatch='IN_MODIFY,IN_ATTRIB,IN_CREATE,IN_DELETE,IN_DONT_FOLLOW,IN_ONLYDIR,IN_MOVED_FROM,IN_MOVED_TO,IN_MOVE'
script="$pwd/incron-test.php"
arguments='$@ $# $%';
incronLine="$folderToWatch $eventsToWatch $script $arguments"
echo $incronLine | incrontab -
echo '--- incrontab now contains ---'
incrontab --list

