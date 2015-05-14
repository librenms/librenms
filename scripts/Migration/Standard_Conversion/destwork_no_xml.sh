#!/bin/bash
# Observium to LibreNMS conversion

####################### SCRIPT DESCRIPTION ########################
# A simple script to add each host in text file to LibreNMS       #
###################################################################

########################### DIRECTIONS ############################
# Enter values for ADDHOST, SNMPSTRING, and NODELIST. The default # 
# should work if you put the files in the same location.          #
###################################################################

############################# CREDITS #############################             
# LibreNMS work is done by a great group - http://librenms.org    #
# Script Written by - Dan Brown - http://vlan50.com               #
###################################################################

# Enter path to LibreNMS addhost module
ADDHOST=/opt/librenms/addhost.php
# Enter your unique SNMP String
SNMPSTRING=cisconetwork
# Enter path to nodelist text file
NODELIST=/tmp/nodelist.txt

while read line 
	# Add each host from the node list file to LibreNMS
	do php $ADDHOST "${line%/*}" $SNMPSTRING v2c;
done < $NODELIST