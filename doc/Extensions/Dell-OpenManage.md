source: Extensions/Dell-OpenManage.md
path: blob/master/doc/

# Dell OpenManage Support

For Dell OpenManage support you will need to install Dell OpenManage
(yeah - really :)) (minimum 5.1) onto the device you want to
monitor. Ensure that net-snmp is using srvadmin, you should see
something similar to:

```bash
master agentx
view all included .1
access notConfigGroup "" any noauth exact all none none
smuxpeer .1.3.6.1.4.1.674.10892.1
```

Restart net-snmp:

```bash
service snmpd restart
```

Ensure that srvadmin is started, this is usually done by executing:

```bash
/opt/dell/srvadmin/sbin/srvadmin-services.sh start
```

Once this has been done, add the device to LibreNMS as normal and you
will start to receive Temperatures and Fanspeed data.

# Windows

Download OpenManage from Dell's support page
[Link](http://www.dell.com/support/contents/us/en/04/article/product-support/self-support-knowledgebase/enterprise-resource-center/systemsmanagement/OMSA)
and install OpenManage onyour windows server. Make sure you have SNMP
setup and running on your windows server.



