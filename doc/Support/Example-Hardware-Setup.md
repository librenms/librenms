source: Support/Example-Hardware-Setup.md
path: blob/master/doc/

# Example hardware setups

The information in this document is direct from users, it's a place for people to share their
setups so you have an idea of what may be required for your install.

To obtain the device, port and sensor counts you can run:

```mysql
select count(*) from devices;
select count(*) from ports where `deleted` = 0;
select count(*) from sensors where `sensor_deleted` = 0;
```

## [laf](https://github.com/laf)

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

## [Vente-Privée](https://github.com/vp-noc)

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

## [KKrumm](https://github.com/kkrumm1)

> Home

|                | LibreNMS            | MySQL               |
| -------------- | ------------------- | ------------------- |
| Type           | VM                  | Same Server         |
| OS             | CentOS 7            |                     |
| CPU            | 2 Sockets, 4 Cores  |                     |
| Memory         | 4GB                 |                     |
| Disk Type      | Raid 10, SAS Drives |                     |
| Disk Space     | 40 GB               |                     |
| Devices        | 12                  |                     |
| Ports          | 130                 |                     |
| Health sensors | 44                  |                     |
| Load           | < 2.5               |                     |

## [KKrumm](https://github.com/kkrumm1)

> Work

|                | LibreNMS            | MySQL               |
| -------------- | ------------------- | ------------------- |
| Type           | HP Proliantdl380gen8| Same Server         |
| OS             | CentOS 7            |                     |
| CPU            | 2 Sockets, 24 Cores |                     |
| Memory         | 32GB                |                     |
| Disk Type      | Raid 10, SAS Drives |                     |
| Disk Space     | 250 GB              |                     |
| Devices        | 390                 |                     |
| Ports          | 16167               |                     |
| Health sensors | 3223                |                     |
| Load           | < 14.5              |                     |

## [CppMonkey(KodApa85)](https://github.com/cppmonkey)

> Home

|                | LibreNMS            | MariaDB             |
| -------------- | ------------------- | ------------------- |
| Type           | i5-4690K            | Same Workstation    |
| OS             | Ubuntu 18.04.2      |                     |
| CPU            | 4 Cores             |                     |
| Memory         | 16GB                |                     |
| Disk Type      | Hybrid SATA         |                     |
| Disk Space     | 2 TB                |                     |
| Devices        | 14                  |                     |
| Ports          | 0                   |                     |
| Health sensors | 70                  |                     |
| Load           | < 0.5               |                     |

## [CppMonkey(KodApa85)](https://github.com/cppmonkey)

> Dev

Running in Ganeti

|                | LibreNMS            | MariaDB             |
| -------------- | ------------------- | ------------------- |
| Type           | VM                  | Same VM             |
| OS             | CentOS 7.5          |                     |
| CPU            | 2 Cores             |                     |
| Memory         | 4GB                 |                     |
| Disk Type      | M.2                 |                     |
| Disk Space     | 40 GB               |                     |
| Devices        | 38                  |                     |
| Ports          | 1583                |                     |
| Health sensors | 884                 |                     |
| Load           | < 1.0               |                     |

## [CppMonkey(KodApa85)](https://github.com/cppmonkey)

> Work NOC

Running in Ganeti Cluster with 2x Dell PER730xd - 64GB, Dual E5-2660 v3

|                | LibreNMS            | MariaDB             |
| -------------- | ------------------- | ------------------- |
| Type           | VM                  | VM                  |
| OS             | Debian Stretch      | Debian Stretch      |
| CPU            | 4 Cores             | 2 Cores             |
| Memory         | 8GB                 | 4GB                 |
| Disk Type      | Raid 6, SAS Drives  |                     |
| Disk Space     | 100 GB              | 40GB                |
| Devices        | 179                 |                     |
| Ports          | 14495               |                     |
| Health sensors | 2329                |                     |
| Load           | < 2.5               | < 1.5               |

## [LaZyDK](https://github.com/lazydk)

> Home

|                | LibreNMS            | MariaDB             |
| -------------- | ------------------- | ------------------- |
| Type           | VM - QNAP TS-453 Pro| Same Server         |
| OS             | Ubuntu 16.04        |                     |
| CPU            | 1 vCore             |                     |
| Memory         | 2GB                 |                     |
| Disk Type      | Raid 1, SATA Drives |                     |
| Disk Space     | 10 GB               |                     |
| Devices        | 26                  |                     |
| Ports          | 228                 |                     |
| Health sensors | 117                 |                     |
| Load           | < 0.92              |                     |

## [SirMaple](https://github.com/sirmaple)

> Home

|                | LibreNMS            | MariaDB             |
| -------------- | ------------------- | ------------------- |
| Type           | VM                  | Same Server         |
| OS             | Ubuntu 18.04 LTS    |                     |
| CPU            | 2 vCore             |                     |
| Memory         | 1GB                 |                     |
| Disk Type      | Raid 1, SSD         |                     |
| Disk Space     | 25 GB               |                     |
| Devices        | 30                  |                     |
| Ports          | 196                 |                     |
| Health sensors | 207                 |                     |
| Load           | < 3.65              |                     |

## [VVelox](https://github.com/VVelox)

> Home / Dev

|                | LibreNMS            | MariaDB             |
| -------------- | ------------------- | ------------------- |
| Type           | Supermicro X7SPA-HF | Same Server         |
| OS             | FreeBSD 12-STABLE   |                     |
| CPU            | Intel Atom D525     |                     |
| Memory         | 4GB                 |                     |
| Disk Type      | Raid 1, SATA        |                     |
| Disk Space     | 1TB                 |                     |
| Devices        | 17                  |                     |
| Ports          | 174                 |                     |
| Health sensors | 76                  |                     |
| Load           | < 3                 |                     |

## [SourceDoctor](https://github.com/SourceDoctor)

> Home / Dev

Running in VMWare Workstation Pro

|                | LibreNMS            | MariaDB             |
| -------------- | ------------------- | ------------------- |
| Type           | VM                  | Same Server         |
| OS             | Debian Buster       |                     |
| CPU            | 2 vCore             |                     |
| Memory         | 2GB                 |                     |
| Disk Type      | Raid 5, SSD         |                     |
| Disk Space     | 20GB                |                     |
| Devices        | 35                  |                     |
| Ports          | 245                 |                     |
| Health sensors | 101                 |                     |
| Load           | < 1                 |                     |

## [lazyb0nes](https://github.com/lazyb0nes)

Lab

|                | LibreNMS            | MariaDB             |
| -------------- | ------------------- | ------------------- |
| Type           | VM                  | Same Server         |
| OS             | RHEL 7.7            |                     |
| CPU            | 32 cores            |
| Memory         | 64GB                |                     |
| Disk Type      | Flash San Array     |                     |
| Disk Space     | 400GB               |                     |
| Devices        | 670                 |                     |
| Ports          | 25678               |                     |
| Health sensors | 2457                |                     |
| Load           | 10.92               |                     |

## [dagb](https://github.com/dagbdagb)

> Work

Running in VMware.

|                | LibreNMS            | MariaDB             |
| -------------- | ------------------- | ------------------- |
| Type           | Virtual             | Same Server         |
| OS             | CentOS 7            |                     |
| CPU            | 12 Cores Xeon 6130  |                     |
| Memory         | 8GB                 |                     |
| Disk Type      | SAN (SSD)           |                     |
| Disk Space     | 26GB/72GB/7GB       | (logs/RRDs/db)      |
| Devices        | 650                 |                     |
| Ports          | 34300               |                     |
| Health sensors | 10500               |                     |
| Load           | 5.5 (45%)           |                     |
