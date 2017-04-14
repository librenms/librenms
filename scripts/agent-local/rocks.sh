#!/bin/bash
############################################################
# copy this file somewhere like /opt and chmod +x it       #
# edit your snmpd.conf and add the below line and restart: #
# extend ogs /opt/rocks.sh                                 #
############################################################

# required
source /etc/profile.d/sge-binaries.sh;

QSTAT="/opt/gridengine/bin/linux-x64/qstat"
RUNNING_JOBS=$($QSTAT -u "*" -s r | wc -l)
PENDING_JOBS=$($QSTAT -u "*" -s p | wc -l)
SUSPEND_JOBS=$($QSTAT -u "*" -s s | wc -l)
ZOMBIE_JOBS=$($QSTAT -u "*" -s z | wc -l)

echo $RUNNING_JOBS;
echo $PENDING_JOBS;
echo $SUSPEND_JOBS;
echo $ZOMBIE_JOBS;
