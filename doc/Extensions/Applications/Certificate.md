## Certificate

A small python3 script that checks age and remaining validity of certificates


=== "Debian/Ubuntu"
    This script needs following packages on Debian/Ubuntu Systems:

    ```bash
    apt-get install python3 python3-openssl
    ```


Content of an example /etc/snmp/certificate.json . Please edit with your own settings.

```json
{"domains": [
    {"fqdn": "www.mydomain.com"},
    {"fqdn": "some.otherdomain.org",
     "port": 8443},
    {"fqdn": "personal.domain.net"},
    {"fqdn": "selfsignedcert_host.domain.com",
     "cert_location": "/etc/pki/tls/certs/localhost.pem"}
]
}
```

a. (Required): Key 'domains' contains a list of domains to check.
b. (Optional): You can define a port. By default it checks on port 443.
c. (Optional): You may define a certificate location for self-signed certificates.

### SNMP Extend
1. Copy the shell script to the desired host.

```bash
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/certificate.py -O /etc/snmp/certificate.py
```

2. Make the script executable

```bash
chmod +x /etc/snmp/certificate.py
```

3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```bash
extend certificate /etc/snmp/certificate.py
```
4. Restart snmpd on your host

The application should be auto-discovered as described at the top of the page. If it is not, please follow the steps set out under `SNMP Extend` heading top of page.