
## Seafile

SNMP extend script to monitor your Seafile Server

### SNMP Extend

1. Copy the Python script, seafile.py, to the desired host
```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/seafile.py -O /etc/snmp/seafile.py
```

Also you have to install the requests Package for Python3.
Under Ubuntu/Debian just run `apt install python3-requests`

2. Make the script executable
```
chmod +x /etc/snmp/seafile.py
```

3. Edit your snmpd.conf file and add:
```
extend seafile /etc/snmp/seafile.py
```

4. You will also need to create the config file, which is named
seafile.json . The script has to be located at /etc/snmp/.

```json
{"url": "https://seafile.mydomain.org",
 "username": "some_admin_login@mail.address",
 "password": "password",
 "account_identifier": "name"
 "hide_monitoring_account": true
}
```

The variables are as below.

| Variable | Description |
| --- | --- |
| url | Url how to get access to Seafile Server |
| username | Login to Seafile Server.<br>It is important that used Login has admin privileges.<br>Otherwise most API calls will be denied. |
| password | Password to the configured login. |
| account_identifier | Defines how user accounts are listed in RRD Graph.<br>Options are: name, email |
| hide_monitoring_account | With this Boolean you can hide the Account which you<br>use to access Seafile API |

!!! note
    It is recommended to use a dedicated Administrator account for monitoring.
