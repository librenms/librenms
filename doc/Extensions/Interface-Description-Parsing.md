Table of Content:
-   [About](#about)
   -   [Setup](#setup)
-   [Keywords](#keywords)
   -   [Type-keywords](#type-keywords)
   -   [Info-keywords](#info-keywords)
-   [Examples](#examples)
-   [Sourcecode](#source)

# <a name="about">About</a>:

Librenms can interpret, display and group certain additional information on ports.
For this a small `bash` script is supplied in `scripts/` called `ifAlias`.

<a name="setup">Setup</a>:

This requires a little bit of setup on the monitored Server (Not the server running librenms!):

*   Add `ifAlias` from `/opt/librenms/scripts/` or download it from [here](#source) to the Server and make
    it executeable `chmod +x /path/to/ifAlias`
*   Add to `snmpd.conf` something like:
    ``pass .1.3.6.1.2.1.31.1.1.1.18 /path/to/ifAlias``
*   Restart your `net-snmpd`

There are no changes to be made or additions to install for the polling librenms.

Now you can set up your [keywords](#keywords) in your `/etc/network/interfaces`
``//Add more distributions than just Debian based``

# <a name="keywords">Keywords</a>:

See [examples](#examples) for formats.

* <a name="type-keywords">Type-keywords</a>:
 * `Cust`    - Customer
 * `Transit` - Transit link
 * `Peering` - Peering link
 * `Core`    - Infrastructure link (non-customer)
 * `Server`  - Server link (non-customer)
* <a name="info-keywords">Info-keywords</a>:
 * `()` contains a note
 * `{}` contains *your* circuit id
 * `[]` contains the service type or speed

# <a name="examples">Examples</a>:
```text
# eth3: Cust: Example Customer [10Mbit] (T1 Telco Y CCID129031) {EXAMP0001}`
# eth0: Transit: Example Provider (AS65000)`
# eth1: Core: core.router01 FastEthernet0/0 (Telco X CCID023141)`
# eth2: Peering: Peering Exchange
```

# <a name="source">Sourcecode</a>:

* https://github.com/librenms/librenms/blob/master/scripts/ifAlias
