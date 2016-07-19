#!/bin/bash
################################################################
# copy this script to somewhere like /opt and make chmod +x it #
# edit your snmpd.conf add the below line and restart snmpd    #
# extend osupdate /opt/os-updates.sh                           #
################################################################ 
BIN_AWK='/usr/bin/awk'
BIN_WC='/usr/bin/wc'
CMD_WC='-l'
BIN_ZYPPER='/usr/bin/zypper'
CMD_ZYPPER='lu'
BIN_YUM='/usr/bin/yum'
CMD_YUM='check-update'
BIN_APT='/usr/bin/apt'
CMD_APT='list --upgradable'

if [ -f /etc/os-release ]; then
	OS=`$BIN_AWK -F= '/^ID=/{print $2}' /etc/os-release`
	if [ $OS == "opensuse" ]; then
		UPDATES=`$BIN_ZYPPER $CMD_ZYPPER | $BIN_WC $CMD_WC`
		if [ $UPDATES -gt 3 ]; then
			echo $(($UPDATES-3));
		else
			echo "0";
		fi
	elif [ $OS == "\"centos\"" ]; then
		UPDATES=`$BIN_YUM $CMD_YUM | $BIN_WC $CMD_WC`
		if [ $UPDATES -gt 6 ]; then
			echo $(($UPDATES-6));
		else
			echo "0";
		fi
	elif [ $OS == "ubuntu" ]; then
		UPDATES=`$BIN_APT $CMD_APT | $BIN_WC $CMD_WC`
		if [ $UPDATES -gt 1 ]; then
			echo $(($UPDATES-1));
		else
			echo "0";
		fi
	fi
else
	echo "0";
fi
