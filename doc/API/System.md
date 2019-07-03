source: API/System.md
path: blob/master/doc/

### `system`

Display Librenms instance information.

Route: `/api/v0/system`

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/system
```

Output:

```json
{
    "status": "ok",
    "system": [
        {
            "local_ver": "1.37-234-g19103ee",
            "local_sha": "19103ee36f68f009272c15be22e5a7e10a8b0b85",
            "local_date": "1526480966",
            "local_branch": "master",
            "db_schema": 249,
            "php_ver": "7.2.2",
            "mysql_ver": "5.5.56-MariaDB",
            "rrdtool_ver": "1.4.8",
            "netsnmp_ver": "NET-SNMP 5.7.2"
        }
    ],
    "count": 1
}
```
