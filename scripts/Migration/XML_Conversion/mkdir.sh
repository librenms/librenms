#!/usr/bin/env bash
# Observium to LibreNMS conversion

####################### SCRIPT DESCRIPTION ########################
# A simple script to create needed directories on LibreNMS server #
###################################################################

########################### DIRECTIONS ############################
# Enter values for NODELIST, L_RRDPATH. The default should work if# 
# you put the files in the same location.                         #
###################################################################

############################# CREDITS #############################             
# LibreNMS work is done by a great group - https://www.librenms.org    #
# Script Written by - Dan Brown - http://vlan50.com               #
###################################################################

# Enter path to node list text file
NODELIST=/tmp/nodelist.txt
# Enter path to LibreNMS RRD directories
L_RRDPATH=/opt/librenms/rrd/

# This loop enters the RRD folder and creates dir based on contents of node list text file
while read line 
	do mkdir -p $L_RRDPATH"${line%/*}"
done < $NODELIST
