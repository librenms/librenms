#!/usr/bin/env bash
# Observium to LibreNMS conversion

####################### SCRIPT DESCRIPTION ########################
# This script converts the XML files from Observium back to RRD   #
# files for use with LibreNMS. It then adds the device using the  #
# Addhost function of LibreNMS                                    #
###################################################################

########################### DIRECTIONS ############################
# Enter values for L_RRDPATH, ADDHOST, SNMPSTRING, and NODELIST.  #
#The default should work if you put the files in the same location#
###################################################################

############################# CREDITS #############################
# LibreNMS work is done by a great group - https://www.librenms.org    #
# Script Written by - Dan Brown - http://vlan50.com               #
###################################################################

# Enter path to LibreNMS RRD directories
L_RRDPATH=/opt/librenms/rrd/
# Enter your unique SNMP String
SNMPSTRING=cisconetwork
# Enter SNMP version of all clients in nodelist text file
SNMPVERSION=v2c
# Enter path to nodelist text file
NODELIST=/tmp/nodelist.txt
# Enter user and group of LibreNMS installation
L_USRGRP=librenms

# Loop enters RRD directory and then each folder based on contents of node list text file
while read -r line
	# Enter the directory
	do cd $L_RRDPATH"${line%/*}" || return 1
		# Convert from XML back to RRD
		for f in *.xml; do rrdtool restore "${f}" "$(echo "${f}" | cut -f1 -d .)".rrd; done;
		# Remove leftover XML files
		rm ./*.xml;
		# Change ownership to LibreNMS user and group
		chown -R $L_USRGRP:$L_USRGRP .;
		# Add the host to LibreNMS
		lnms device:add --$SNMPVERSION -c$SNMPSTRING "${line%/*}"
		# Change back to parent directory
		cd ..;
	done < $NODELIST
