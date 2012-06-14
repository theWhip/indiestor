#!/bin/bash
incrontab --remove
pwd=$(pwd)
echo $pwd

#general vars
script="$pwd/incron-test2.php"
arguments='$@ $# $%';

#main folder
mainWatched="$pwd/folder-watched"
mainEventsToWatch='IN_ATTRIB,IN_CREATE,IN_DELETE,IN_DONT_FOLLOW,IN_ONLYDIR,IN_MOVED_FROM,IN_MOVED_TO'
mainIncronLine="$mainWatched $mainEventsToWatch $script MAIN $arguments"

#mxf folder
mxfWatched="$mainWatched/Avid\ MediaFiles/MXF"
mxfEventsToWatch='IN_ATTRIB,IN_CREATE,IN_DELETE,IN_DONT_FOLLOW,IN_ONLYDIR,IN_MOVED_FROM,IN_MOVED_TO'
mxfIncronLine="$mxfWatched $mxfEventsToWatch $script MXF $arguments"

#in move to pending
ImpWatched="$pwd/in_move_pending"
ImpEventsToWatch='IN_CREATE'
ImpIncronLine="$ImpWatched $ImpEventsToWatch $script IMP $arguments"

#all incronlines
allIncronLines="$mainIncronLine\n$mxfIncronLine\n$ImpIncronLine"

echo -e $allIncronLines | incrontab -
echo '--- incrontab now contains ---'
incrontab --list

