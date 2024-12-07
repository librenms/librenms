# Syslog support


## Syslog integration variants
This section explain different ways to recieve and process syslog with LibreNMS.
Except of graylog, all Syslogs variants store their logs in the LibreNMS database. You need to enable the Syslog extension in  `config.php`:

```php
$config['enable_syslog'] = 1;
```
A Syslog integration gives you a centralized view of information within the LibreNMS (device view, traps, event). Further more you can trigger alerts based on syslog messages (see rule collections).

### Traditional Syslog server

#### syslog-ng
=== "Debian / Ubuntu"
    ```ssh
    apt-get install syslog-ng-core
    ```
=== "CentOS / RedHat"
    ```ssh
    yum install syslog-ng
    ```

Once syslog-ng is installed, create the config file 
(/etc/syslog-ng/conf.d/librenms.conf) and paste the following:

```bash
source s_net {
        tcp(port(514) flags(syslog-protocol));
        udp(port(514) flags(syslog-protocol));
};

destination d_librenms {
        program("/opt/librenms/syslog.php" template ("$HOST||$FACILITY||$PRIORITY||$LEVEL||$TAG||$R_YEAR-$R_MONTH-$R_DAY $R_HOUR:$R_MIN:$R_SEC||$MSG||$PROGRAM\n") template-escape(yes));
};

log {
        source(s_net);
        source(s_src);
        destination(d_librenms);
};
```

Next start syslog-ng:

```ssh
service syslog-ng restart
```

If no messages make it to the syslog tab in LibreNMS, chances are you experience an issue with SELinux. If so, create a file mycustom-librenms-rsyslog.te , with the following content:

```
module mycustom-librenms-rsyslog 1.0;

require {
        type syslogd_t;
        type httpd_sys_rw_content_t;
        type ping_exec_t;
        class process execmem;
        class dir { getattr search write };
        class file { append getattr execute open read };
}

#============= syslogd_t ==============
allow syslogd_t httpd_sys_rw_content_t:dir { getattr search write };
allow syslogd_t httpd_sys_rw_content_t:file { open read append getattr };
allow syslogd_t self:process execmem;
allow syslogd_t ping_exec_t:file execute;
```

Then, as root, execute the following commands:

```ssh
checkmodule -M -m -o mycustom-librenms-rsyslog.mod mycustom-librenms-rsyslog.te
semodule_package -o mycustom-librenms-rsyslog.pp -m mycustom-librenms-rsyslog.mod
semodule -i mycustom-librenms-rsyslog.pp
```


#### rsyslog

If you prefer rsyslog, here are some hints on how to get it working.

Add the following to your rsyslog config somewhere (could be at the
top of the file in the step below, could be in `rsyslog.conf` if you
are using remote logs for something else on this host)

```
# Listen for syslog messages on UDP:514
$ModLoad imudp
$UDPServerRun 514
```

Create a file called `/etc/rsyslog.d/30-librenms.conf`and add the following depending on your version of rsyslog.

=== "Version 8"
    ```
    # Feed syslog messages to librenms
    module(load="omprog")

    template(name="librenms"
            type="string"
            string= "%fromhost%||%syslogfacility%||%syslogpriority%||%syslogseverity%||%syslogtag%||%$year%-%$month%-%$day% %timegenerated:8:25%||%msg%||%programname%\n")
            action(type="omprog"
            binary="/opt/librenms/syslog.php"
            template="librenms")

    & stop
    ```

=== "Version 7"
    ```
    #Feed syslog messages to librenms
    $ModLoad omprog

    $template librenms,"%fromhost%||%syslogfacility%||%syslogpriority%||%syslogseverity%||%syslogtag%||%$year%-%$month%-%$day% %timegenerated:8:25%||%msg%||%programname%\n"

    *.* action(type="omprog" binary="/opt/librenms/syslog.php" template="librenms")

    & stop

    ```

=== "Legacy"
    ```
    # Feed syslog messages to librenms
    $ModLoad omprog
    $template librenms,"%FROMHOST%||%syslogfacility-text%||%syslogpriority-text%||%syslogseverity%||%syslogtag%||%$YEAR%-%$MONTH%-%$DAY%    %timegenerated:8:25%||%msg%||%programname%\n"

    $ActionOMProgBinary /opt/librenms/syslog.php
    *.* :omprog:;librenms
    ```

If your rsyslog server is receiving messages relayed by another syslog
server, you may try replacing `%fromhost%` with `%hostname%`, since
`fromhost` is the host the message was received from, not the host
that generated the message.  The `fromhost` property is preferred as
it avoids problems caused by devices sending incorrect hostnames in
syslog messages.

### Local Logstash

If you prefer logstash, and it is installed on the same server as
LibreNMS, here are some hints on how to get it working.

First, install the output-exec plugin for logstash:

```bash
/usr/share/logstash/bin/logstash-plugin install logstash-output-exec
```

Next, create a logstash configuration file
(ex. /etc/logstash/conf.d/logstash-simple.conf), and add the
following:

```
input {
syslog {
    port => 514
  }
}


output {
        exec {
        command => "echo `echo %{host},,,,%{facility},,,,%{priority},,,,%{severity},,,,%{facility_label},,,,``date --date='%{timestamp}' '+%Y-%m-%d %H:%M:%S'``echo ',,,,%{message}'``echo ,,,,%{program} | sed 's/\x25\x7b\x70\x72\x6f\x67\x72\x61\x6d\x7d/%{facility_label}/'` | sed 's/,,,,/||/g' | /opt/librenms/syslog.php &"
        }
        elasticsearch {
        hosts => ["10.10.10.10:9200"]
        index => "syslog-%{+YYYY.MM.dd}"
        }
}
```

Replace 10.10.10.10 with your primary elasticsearch server IP, and set
the incoming syslog port. Alternatively, if you already have a
logstash config file that works except for the LibreNMS export, take
only the "exec" section from output and add it.

### Remote Logstash (or any json source)
If you have a large logstash / elastic installation for collecting and filtering syslogs, you can simply pass the relevant logs as json to the LibreNMS API "syslog sink". This variant may be more flexible and secure in transport. It does not require any major changes to existing ELK setup. You can also pass simple json kv messages from any kind of application or script (example below) to this sink. 

For long term or advanced aggregation searches you might still use Kibana/Grafana/Graylog etc. It is recommended to keep `config['syslog_purge']` short.

A schematic setup can look like this:
```
  ┌──────┐
  │Device├─►┌───────────────────┐                ┌──────────────┐
  └──────┘  │Logstash Cluster   ├┬──────────────►│ElasticSearch ├┐
            │  RabbitMQ         ││               │ Cluster      ││
 ┌──────┬──►│    Filtering etc  ││ ───────┐      └┬─────────────┼│
 │Device│   └┬──────────────────┼│        │       └──────────────┘
 └──────┘    └───────────────────┘        ▼
                                      ~~~WAN~~~
                                          │
                                        ┌─┼─┐
                                        │┼┼┼│ LB / Firewall / etc
                                        └─┼─┘
                                          │
                                          ▼
                         ┌────────────────────┐    ┌────────────────────┐
                         │LibreNMS Sink       ├┬──►│LibreNMS Master     │
                         │/api/v0/syslogsink/ ││   │ MariaDB            │
                         └┬───────────────────┼│   └────────────────────┘
                          └────────────────────┘
```

A minimal [Logstash http output](https://www.elastic.co/guide/en/logstash/current/plugins-outputs-http.html) configuration can look like this: 
```
output {
....
        #feed it to LibreNMS
     	http {
     		http_method => "post"
     		url => "https://sink.librenms.org/api/v0/syslogsink/    # replace with your librenms host
     		format => "json_batch"                                  # put multiple syslogs in on HTTP message
                retry_failed => false                               # if true, logstash is blocking if the API is unavailable, be careful! 
                headers => ["X-Auth-Token","xxxxxxxLibreNMSApiToken]
                
                # optional if your mapping is not already done before or does not match. "msg" and "host" is mandatory. 
                # you might also use out the clone {} function to duplicate your log stream and a dedicated log filtering/mapping etc.
                # mapping => {
                # "host"=> "%{host}"
                # "program" => "%{program}"
                # "facility" => "%{facility_label}"
                # "priority" => "%{syslog5424_pri}"
                # "level" => "%{facility_label}"				
                # "tag" => "%{topic}"
                # "msg" => "%{message}"
                # "timestamp" => "%{@timestamp}"
                # }
        }
}
```

Sample test data:
```
curl -L -X POST 'https://sink.librenms.org/api/v0/syslogsink/' -H 'X-Auth-Token: xxxxxxxLibreNMSApiToken' --data-raw '[   
    {
        "msg": "kernel: minimum Message",
        "host": "mydevice.fqdn.com"
    },
    {
        "msg": "Line protocol on Interface GigabitEthernet1/0/41, changed state to up",
        "facility": 23,
        "priority": "189",
        "program": "LINEPROTO-5-UPDOWN",
        "host": "172.29.10.24",
        "@timestamp": "2022-12-01T20:14:28.257Z",
        "severity": 5,
        "level": "ERROR"
    },
    {
        "msg": "kernel: a unknown host",
        "host": "unknown.fqdn.com"
    }
]'
```
`msg` and `host` are the minimum keys. 


### Graylog

This variant method use a external Graylog installation and its database. Please refer to the dedicated [Graylog](Graylog.md) documentation.

## Client configuration

Below are sample configurations for a variety of clients. You should
understand the config before using it as you may want to make some
slight changes. Further configuration hints may be found in the file Graylog.md.

Replace librenms.ip with IP or hostname of your LibreNMS install.

Replace any variables in <brackets> with the relevant information.

### syslog

```config
*.*     @librenms.ip
```

### rsyslog

```config
*.* @librenms.ip:514
```

### Cisco ASA

```config
logging enable
logging timestamp
logging buffer-size 200000
logging buffered debugging
logging trap notifications
logging host <outside interface name> librenms.ip
```

### Cisco IOS

```config
logging trap debugging
logging facility local6
logging librenms.ip
```

### Cisco NXOS

```config
logging server librenms.ip 5 use-vrf default facility local6
```

### Juniper Junos

```config
set system syslog host librenms.ip authorization any
set system syslog host librenms.ip daemon any
set system syslog host librenms.ip kernel any
set system syslog host librenms.ip user any
set system syslog host librenms.ip change-log any
set system syslog host librenms.ip source-address <management ip>
set system syslog host librenms.ip exclude-hostname
set system syslog time-format
```

### Huawei VRP

```config
info-center loghost librenms.ip
info-center timestamp debugging short-date without-timezone // Optional
info-center timestamp log short-date // Optional
info-center timestamp trap short-date // Optional
//This is optional config, especially if the device is in public ip and you dont'want to get a lot of messages of ACL
info-center filter-id bymodule-alias VTY ACL_DENY
info-center filter-id bymodule-alias SSH SSH_FAIL
info-center filter-id bymodule-alias SNMP SNMP_FAIL
info-center filter-id bymodule-alias SNMP SNMP_IPLOCK
info-center filter-id bymodule-alias SNMP SNMP_IPUNLOCK
info-center filter-id bymodule-alias HTTP ACL_DENY
```

### Huawei SmartAX (GPON OLT)

```config
loghost add librenms.ip librenms
loghost activate name librenms
```

### Allied Telesis Alliedware Plus

```config
log date-format iso // Required so syslog-ng/LibreNMS can correctly interpret the log message formatting.
log host x.x.x.x
log host x.x.x.x level <errors> // Required. A log-level must be specified for syslog messages to send.
log host x.x.x.x level notices program imish // Useful for seeing all commands executed by users.
log host x.x.x.x level notices program imi // Required for Oxidized Syslog hook log message.
log host source <eth0>
```
    
### HPE/Aruba Procurve
    
```config
configure
logging severity warning
logging facility local6
logging librenms.ip control-descr “LibreNMS”
logging notify running-config-change
write memory
```

If you have permitted udp and tcp 514 through any firewall then that
should be all you need. Logs should start appearing and displayed
within the LibreNMS web UI.

### Windows

By Default windows has no native way to send logs to a remote syslog server.

Using this how to you can download Datagram-Syslog Agent to send logs
to a remote syslog server (LibreNMS).

#### Note

Keep in mind you can use any agent or program to send the logs. We are
just using this Datagram-Syslog Agent for this example.

[Link to How to](http://techgenix.com/configuring-syslog-agent-windows-server-2012/)

You will need to download and install "Datagram-Syslog Agent" for this how to
[Link to Download](http://download.cnet.com/Datagram-SyslogAgent/3001-2085_4-10370938.html)

## External hooks

Trigger external scripts based on specific syslog patterns being
matched with syslog hooks. Add the following to your LibreNMS
`config.php` to enable hooks:

```ssh
$config['enable_syslog_hooks'] = 1;
```

The below are some example hooks to call an external script in the
event of a configuration change on Cisco ASA, IOS, NX-OS and IOS-XR
devices. Add to your `config.php` file to enable.

### Cisco ASA

```ssh
$config['os']['asa']['syslog_hook'][] = Array('regex' => '/%ASA-(config-)?5-111005/', 'script' => '/opt/librenms/scripts/syslog-notify-oxidized.php');
```

### Cisco IOS

```ssh
$config['os']['ios']['syslog_hook'][] = Array('regex' => '/%SYS-(SW[0-9]+-)?5-CONFIG_I/', 'script' => '/opt/librenms/scripts/syslog-notify-oxidized.php');
```

### Cisco NXOS

```ssh
$config['os']['nxos']['syslog_hook'][] = Array('regex' => '/%VSHD-5-VSHD_SYSLOG_CONFIG_I/', 'script' => '/opt/librenms/scripts/syslog-notify-oxidized.php');
```

### Cisco IOSXR

```ssh
$config['os']['iosxr']['syslog_hook'][] = Array('regex' => '/%GBL-CONFIG-6-DB_COMMIT/', 'script' => '/opt/librenms/scripts/syslog-notify-oxidized.php');
```

### Juniper Junos

```ssh
$config['os']['junos']['syslog_hook'][] = Array('regex' => '/UI_COMMIT:/', 'script' => '/opt/librenms/scripts/syslog-notify-oxidized.php');
```

### Juniper ScreenOS

```ssh
$config['os']['screenos']['syslog_hook'][] = Array('regex' => '/System configuration saved/', 'script' => '/opt/librenms/scripts/syslog-notify-oxidized.php');
```

### Allied Telesis Alliedware Plus

**Note:** At least software version 5.4.8-2.1 is required. `log host
x.x.x.x level notices program imi` may also be required depending on
configuration. This is to ensure the syslog hook log message gets sent
to the syslog server.

```ssh
$config['os']['awplus']['syslog_hook'][] = Array('regex' => '/IMI.+.Startup-config saved on/', 'script' => '/opt/librenms/scripts/syslog-notify-oxidized.php');
```
    
### HPE/Aruba Procurve

```ssh
$config['os']['procurve']['syslog_hook'][] = Array('regex' => '/Running Config Change/', 'script' => '/opt/librenms/scripts/syslog-notify-oxidized.php');
```

## Configuration Options
### Syslog Clean Up

Can be set inside of  `config.php`

```php
$config['syslog_purge'] = 30;
```

The cleanup is run by daily.sh and any entries over X days old are
automatically purged. Values are in days. See here for more Clean Up
Options [Link](../Support/Cleanup-options.md)


### Matching syslogs to hosts with different names

In some cases, you may get logs that aren't being associated with the
device in LibreNMS. For example, in LibreNMS the device is known as
"ne-core-01", and that's how DNS resolves. However, the received
syslogs are for "loopback.core-nw".

To fix this issue, you can configure LibreNMS to translate the
incoming syslog hostname into another hostname, so that the logs get
associated with the correct device.

Example:

```ssh
$config['syslog_xlate'] = array(
        'loopback0.core7k1.noc.net' => 'n7k1-core7k1',
        'loopback0.core7k2.noc.net' => 'n7k2-core7k2'
);
```
