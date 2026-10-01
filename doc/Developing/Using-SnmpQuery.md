## SnmpQuery

Use SnmpQuery to get and process SNMP data in LibreNMS. There are
several formats for an OID. We prefer the full textual form, such as
`IF-MIB::ifIndex`. The numeric form and the short form are also valid.

### Actions
At an action, the query runs and returns an SnmpResponse with the data.
SnmpResponse has many options to process and index this data.

SnmpQuery has 4 primary actions:

 - get - it gets one or more full OIDs from the device
 - walk - it walks an OID. It is most useful for a table or a column of a table
 - next - it gets the OID after the given OID
 - translate - it converts an OID between the textual form and the numeric form. It returns a string

### Fetch Options

 - numeric - it gives all OIDs in numeric form
 - numericIndex - it gives all OID indexes in numeric form
 - abortOnFailure - in a walk of several OIDs, it stops at the first failure
 - context - it sets a context for the SNMP query
 - mibDir - it adds a MIB directory
 - mibs - it sets the MIBs of this query
 - allowUnordered - it accepts indexes out of order. This option makes an infinite loop possible
 - device - it selects a different device. By default, SnmpQuery queries the active device


## SnmpResponse

### value

For a response with one value, this method returns that value. For a
response with more values, give an OID to select the value.

##### Examples
 A single value from a single get:
 
    SnmpQuery::get('SNMPv2-MIB::sysName.0')->value();
    "server"

The first value that matches an OID:

    SnmpQuery::walk('IF-MIB::ifTable')->value('IF-MIB::ifIndex');
    "1"

The value of the OID at the given index:

    SnmpQuery::walk('IF-MIB::ifTable')->value('IF-MIB::ifDescr.2');
    "enp7s0"


### values

It returns all values in an array. The key of each value is its OID
from SNMP.

##### Examples

Walk a single column of ifTable. A walk of the whole ifTable also
works, but it returns much data. Note: a table uses the `[]` syntax for
an index. All other objects use the dot syntax.

    SnmpQuery::walk('IF-MIB::ifName')->values();
    [
        "IF-MIB::ifName[1]" => "lo",
        "IF-MIB::ifName[2]" => "enp7s0",
    ]

Get two OIDs and show both:

    SnmpQuery::get(['SNMPv2-MIB::sysObjectID.0', 'SNMPv2-MIB::sysDescr.0'])->values();
    [
        "SNMPv2-MIB::sysObjectID.0" => "NET-SNMP-MIB::netSnmpAgentOIDs.10",
        "SNMPv2-MIB::sysDescr.0" => "Linux 5.15.0-59-generic #62-Ubuntu SMP PREEMPT_DYNAMIC Tue Nov 29 16:25:29 UTC 2022 x86_64",
    ]



### valuesByIndex

Group the values by the full index. 

##### Examples

    SnmpQuery::enumStrings()->walk('IP-MIB::ipAddressTable')->valuesByIndex()
    [
        "ipv4."10.14.32.4"" => [
        "IP-MIB::ipAddressIfIndex" => "3",
        "IP-MIB::ipAddressType" => "unicast",
        "IP-MIB::ipAddressPrefix" => "IP-MIB::ipAddressPrefixOrigin[3][ipv4]["10.14.32.4"][32]",
        ...
    ],
        "ipv4."127.0.0.1"" => [
        "IP-MIB::ipAddressIfIndex" => "1",
        "IP-MIB::ipAddressType" => "unicast",
        "IP-MIB::ipAddressPrefix" => "IP-MIB::ipAddressPrefixOrigin[1][ipv4]["127.0.0.0"][8]",
        ...
    ]

### table

Make a multi dimensional array with an index value as the key to each level.
You can specify a depth to group the values at to make the data easier to work
with, the default is 0.

##### Examples

Group by the default depth 0

    SnmpQuery::walk('IP-MIB::ipAddressTable')->table()
    [
        "IP-MIB::ipAddressIfIndex" => [
            "ipv4" => [
                "10.14.32.4" => "3",
                "127.0.0.1" => "1",
            ],
            "ipv6" => [
                "00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:01" => "1",
                "fd:7a:11:5c:a1:e0:00:00:00:00:00:00:9f:e0:6f:72" => "3",
                "fe:80:00:00:00:00:00:00:ae:5a:17:da:13:74:3d:e0" => "3",
                "fe:80:00:00:00:00:00:00:c2:eb:0b:fe:10:21:67:e3" => "2",
            ],
        ],
        "IP-MIB::ipAddressType" => [
    ...

Group by 2 (which matches the index count for this table)

    SnmpQuery::enumStrings()->walk('IP-MIB::ipAddressTable')->table(2)
    [
        "ipv4" => [
            "10.14.32.4" => [
                "IP-MIB::ipAddressIfIndex" => "3",
                "IP-MIB::ipAddressType" => "unicast",
                "IP-MIB::ipAddressPrefix" => "IP-MIB::ipAddressPrefixOrigin[3][ipv4]["10.14.32.4"][32]",
                ...
            ],
            "127.0.0.1" => [
                "IP-MIB::ipAddressIfIndex" => "1",
                "IP-MIB::ipAddressType" => "unicast",
                "IP-MIB::ipAddressPrefix" => "IP-MIB::ipAddressPrefixOrigin[1][ipv4]["127.0.0.0"][8]",
                ...
            ],
    ...

### mapTable

Map an SNMP table with a callback. The callback receives an
array of row values followed by each individual index.

This is the best method when you want to return a collection of data that matches the rows in an SNMP table.

##### Examples

This example uses `dd()`, that is dump and die. It therefore prints
only the first entry.

    SnmpQuery::enumStrings()->walk('IP-MIB::ipAddressTable')->mapTable(function ($data, $ipAddressAddrType, $ipAddressAddr) {
        dd(get_defined_vars());
        // actual closure should return something, like:
        return $$ipAddressAddrType == 'ipv4' ? new Ipv4Address($ipAddressAddr, $data) : new Ipv6Address($ipAddressAddr, $data);
    });
    [
        "data" => [
            "IP-MIB::ipAddressIfIndex" => "3"
            "IP-MIB::ipAddressType" => "unicast"
            "IP-MIB::ipAddressPrefix" => "IP-MIB::ipAddressPrefixOrigin[3][ipv4]["10.14.32.4"][32]"
            "IP-MIB::ipAddressOrigin" => "manual"
            "IP-MIB::ipAddressStatus" => "preferred"
            "IP-MIB::ipAddressCreated" => "3006"
            "IP-MIB::ipAddressLastChanged" => "3006"
            "IP-MIB::ipAddressRowStatus" => "active"
            "IP-MIB::ipAddressStorageType" => "volatile"
        ]
        "ipAddressAddrType" => "ipv4"
        "ipAddressAddr" => ""10.14.32.4""
    ]

### groupByIndex

Fetch values grouped by the index.  The number of index fields is not detected,
it must be specified, the default is 1.  Mostly used for numeric oids when
the index cannot be detected.

##### Examples

    SnmpQuery::numeric()->walk('IF-MIB::ifTable')->groupByIndex(1)
    [
        1 => [
            ".1.3.6.1.2.1.2.2.1.1.1" => "1",
            ".1.3.6.1.2.1.2.2.1.2.1" => "lo",
            ".1.3.6.1.2.1.2.2.1.3.1" => "24",
            ".1.3.6.1.2.1.2.2.1.4.1" => "65536",
            ".1.3.6.1.2.1.2.2.1.5.1" => "10000000",
            ".1.3.6.1.2.1.2.2.1.6.1" => "",
            ".1.3.6.1.2.1.2.2.1.7.1" => "1",
            ...
        ],
        2 => [
        ".1.3.6.1.2.1.2.2.1.1.2" => "2",
        ".1.3.6.1.2.1.2.2.1.2.2" => "enp7s0",
        ".1.3.6.1.2.1.2.2.1.3.2" => "6",
        ".1.3.6.1.2.1.2.2.1.4.2" => "1500",
    ...

### pluck

Fetch an index to key array of the data.  You can specify an oid to get
one column out of an SNMP table.

##### Examples

In this example, the table IF-MIB::ifTable is indexed by ifIndex, so when we walk the ifName column
and call pluck, we get a nice mapping of ifIndex to ifName

    SnmpQuery::walk('IF-MIB::ifName')->pluck()
    [
        1 => "lo",
        2 => "enp7s0",
    ]

## Handling errors

Functions for checking the results of the SNMP query.

 - isValid - check for issues such as aborted SNMP walks (such as network disconnect) and other things.
 - getExitCode - it gives the exit code of the SNMP process
 - getErrorMessage - it gives the stderr output of the process

