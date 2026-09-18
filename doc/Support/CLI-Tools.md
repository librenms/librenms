# Command line tools

This is a short list of the command line tools. The list can be incomplete.
If a tool is not in the list, ask us about it or send a pull request.

# purge-ports.php

This script gives command line access to the "delete port" function of the
web interface. Use it to clean up old ports after a large network change.
It is also useful during work on the poller functions or the discovery
functions.

```
LibreNMS Port purge tool
-p port_id  Purge single port by it's port-id
-f file     Purge a list of ports, read port-ids from _file_, one on each line
            A filename of - means reading from STDIN.
```

# Querying port IDs from the database

A query against the SQL database is one way to get port IDs.

This query returns all deleted ports from the database:

```bash
echo 'SELECT port_id, hostname, ifDescr FROM ports, devices WHERE devices.device_id = ports.device_id AND deleted = 1' | mysql -h your_DB_server -u your_DB_user -p --skip-column-names your_DB_name
```

If the list of ports is correct, write the list into a file. Then run
`purge-ports.php` with that file as the input:

```
echo 'SELECT port_id FROM ports, devices WHERE devices.device_id = ports.device_id AND deleted = 1' | mysql -h your_DB_server -u your_DB_user -p --skip-column-names your_DB_name > ports_to_delete
./purge-port.php -f ports_to_delete
```
