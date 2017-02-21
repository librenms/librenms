source: Support/Example-Hardware-Setup.md
### Example hardware setups

The information in this document is direct from users, it's a place for people to share their 
setups so you have an idea of what may be required for your install.

To obtain the device, port and sensor counts you can run:

```mysql
select count(*) from devices;
select count(*) from ports where `deleted` = 0;
select count(*) from sensors where `sensor_deleted` = 0;
```

#### [laf](https://github.com/laf)

> Home

Running in Proxmox.

|                | LibreNMS            | MySQL               |
| -------------- | ------------------- | ------------------- |
| Type           | Virtual             | Virtual             |
| OS             | CentOS 7            | CentOS 7            |
| CPU            | 2 Sockets, 4 Cores  | 1 Socket, 2 Cores   |
| Memory         | 2GB                 | 2GB                 |
| Disk Type      | Raid 1, SSD         | Raid 1, SSD         |
| Disk Space     | 18GB                | 30GB                |
| Devices        | 20                  | -                   |
| Ports          | 133                 | -                   |
| Health sensors | 47                  | -                   |
| Load           | < 0.1               | < 0.1               |

#### [Vente-Privée](https://github.com/vp-noc)

> NOC

|                | LibreNMS            | MariaDB             |
| -------------- | ------------------- | ------------------- |
| Type           | Dell R430           | Dell R430           |
| OS             | Debian 7 (dotdeb)   | Debian 7 (dotdeb)   |
| CPU            | 2 Sockets, 14 Cores | 1 Socket, 2 Cores   |
| Memory         | 256GB               | 256GB               |
| Disk Type      | Raid 10, SSD        | Raid 10, SSD        |
| Disk Space     | 1TB                 | 1TB                 |
| Devices        | 1028                | -                   |
| Ports          | 26745               | -                   |
| Health sensors | 6238                | -                   |
| Load           | < 0.5               | < 0.5               |
