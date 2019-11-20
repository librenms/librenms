source: Support/CLI-Tools.md
path: blob/master/doc/

# Command line tools

Here's a brief list of command line tools, some might be missing.
If you think something is missing, feel free to ask us or send a pull request :-)

# purge-ports.php

This script provides CLI access to the "delete port" function of the WebUI.
This might come in handy when trying to clean up old ports after large changes
within the network or when hacking on the poller/discovery functions.

```
LibreNMS Port purge tool
-p port_id  Purge single port by it's port-id
-f file     Purge a list of ports, read port-ids from _file_, one on each line
            A filename of - means reading from STDIN.
```

# Querying port IDs from the database

One simple way to obtain port IDs is by querying the SQL database.

If you wanted to query all deleted ports from the database, you could to
this with the following query:

```bash
echo 'SELECT port_id, hostname, ifDescr FROM ports, devices WHERE devices.device_id = ports.device_id AND deleted = 1' | mysql -h your_DB_server -u your_DB_user -p --skip-column-names your_DB_name
```

When you are sure that the list of ports is correct and you want to
delete all of them, you can write the list into a file and call
purge-ports.php with that file as input:

```
echo 'SELECT port_id FROM ports, devices WHERE devices.device_id = ports.device_id AND deleted = 1' | mysql -h your_DB_server -u your_DB_user -p --skip-column-names your_DB_name > ports_to_delete
./purge-ports.php -f ports_to_delete
```
