#!/bin/bash
#############################################################
# - put this file on the nfs server somewhere like /opt/    #
# - edit your snmpd.conf and add line                       #
#  extend nfsstat /opt/nfsstats.sh                          #
# - restart snmpd                                           #
# - make sure that you have all the binaries required below #
#############################################################

CFG_NFSVER='3'
BIN_NFSSTAT='/usr/sbin/nfsstat'
BIN_TR='/usr/bin/tr'
BIN_CUT='/usr/bin/cut'
BIN_GREP='/usr/bin/grep'
BIN_PASTE='/usr/bin/paste'
BIN_RM='/usr/bin/rm'
BIN_MV='/usr/bin/mv'
LOG_OLD='/tmp/nfsstats_old'
LOG_NEW='/tmp/nfsstats_new'

$BIN_NFSSTAT -$CFG_NFSVER -n -l | $BIN_TR -s " " | $BIN_CUT -d ' ' -f 5 | $BIN_GREP -v '^$' > $LOG_NEW 2>&1

$BIN_PASTE $LOG_NEW $LOG_OLD | while read a b ; do
  echo $(($a - $b))  
done

$BIN_RM $LOG_OLD 2>&1
$BIN_MV $LOG_NEW $LOG_OLD 2>&1
