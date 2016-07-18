#!/bin/bash
################################################################
# copy this script to somewhere like /opt and make chmod +x it #
# edit your snmpd.conf add the below line and restart snmpd    #
# extend dhcpstats /opt/dhcp-status.sh                         #
# please change the FILE_DHCP line to your OS leases location  #
################################################################ 
FILE_DHCP='/var/lib/dhcp/db/dhcpd.leases'
BIN_CAT='/usr/bin/cat'
BIN_GREP='/usr/bin/grep'
BIN_WC='/usr/bin/wc'
CMD_WC='-l'
DHCP_LEASES='^lease'
DHCP_ACTIVE='active'
DHCP_BACKUP='backup'
DHCP_FREE='free'

$BIN_CAT $FILE_DHCP | $BIN_GREP $DHCP_LEASES | $BIN_WC $CMD_WC
$BIN_CAT $FILE_DHCP | $BIN_GREP $DHCP_ACTIVE | $BIN_WC $CMD_WC
$BIN_CAT $FILE_DHCP | $BIN_GREP $DHCP_BACKUP | $BIN_WC $CMD_WC
$BIN_CAT $FILE_DHCP | $BIN_GREP $DHCP_FREE | $BIN_WC $CMD_WC
