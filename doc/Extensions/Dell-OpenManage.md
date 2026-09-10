# Dell OpenManage Support

For Dell OpenManage support, install Dell OpenManage 5.1 or later on
the device to monitor. Make sure that net-snmp uses srvadmin. The
configuration looks like this:

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

Make sure that srvadmin runs. This command usually starts it:

```bash
/opt/dell/srvadmin/sbin/srvadmin-services.sh start
```

Then add the device to LibreNMS in the normal way. LibreNMS then
collects the temperature data and the fan speed data.

## Windows

Download OpenManage from Dell's support page
[Link](http://www.dell.com/support/contents/us/en/04/article/product-support/self-support-knowledgebase/enterprise-resource-center/systemsmanagement/OMSA)
and install OpenManage on your Windows server. Also install and start
[SNMP](../Support/SNMP-Configuration-Examples.md#windows-server-2012-r2-and-newer)
on your Windows server.



