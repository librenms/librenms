#### Please note

> Please read this information and delete it once ready.

If you issue is a request for us to add a new device then please ensure you provide the following information as pastebin links.

Please replace the relevant information in these commands.

```bash
./discovery.php -h HOSTNAME -d -m os
./poller.php -h HOSTNAME -r -f -d -m os
snmpbulkwalk -On -v2c -c COMMUNITY HOSTNAME .
```

If possible please also provide what the OS name should be if it doesn't exist already.


