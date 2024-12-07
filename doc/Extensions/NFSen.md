# NFSen

> The installation of NFSen is out of scope for this document / LibreNMS

## Configuration

The following is the configuration that can be used:

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_enable true
    lnms config:set nfsen_split_char '_'
    lnms config:set nfsen_rrds.+ '/var/nfsen/profiles-stat/live/'
    lnms config:set nfsen_rrds.+ '/var/nfsen/profiles-stat'
    lnms config:set nfsen_base.+ '/var/nfsen/'
    lnms config:set nfsen_suffix '_yourdomain_com'
    ```

Set `lnms config:set nfsen_enable true` to enable NFSen support.

`nfsen_rrds` This value tells us where your NFSen rrd files
live. This can also be an array to specify more directories like:

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_rrds.+ '/var/nfsen/profiles-stat/sitea/'
    lnms config:set nfsen_rrds.+ '/var/nfsen/profiles-stat/siteb/'
    ```

Although for most setups, it will look like below, with the
profiles-stat/live directory being where it stores the general RRDs
for data sources.

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_rrds.+ '/var/nfsen/profiles-stat/live'
    ```

If you wish to render info for configure channels for a device, you
need add the various profile-stat directories your system uses, which
for most systems will be as below.

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_rrds.+ '/var/nfsen/profiles-stat'
    ```

When adding sources to nfsen.conf, it is important to use the hostname
that matches what is configured in LibreNMS, because the rrd files
NfSen creates is named after the source name (ident), and it doesn't
allow you to use an IP address instead. However, in LibreNMS, if your
device is added by an IP address, add your source with any name of
your choice, and create a symbolic link to the rrd file.

```bash
cd /var/nfsen/profiles-stat/sitea/
ln -s mychannel.rrd librenmsdeviceIP.rrd
```

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_split_char '_'
    ```

This value tells us what to replace the full stops `.` in the devices
hostname with.

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_suffix '_yourdomain_com'
    ```

The above is a very important bit as device names in NfSen are limited
to 21 characters. This means full domain names for devices can be very
problematic to squeeze in, so therefor this chunk is usually removed.

On a similar note, NfSen profiles for channels should be created with
the same name.

## Stats Defaults and Settings

Below are the default settings used with nfdump for stats.

For more defaulted information on that, please see nfdump(1).  
The default location for nfdump is `/usr/bin/nfdump`. If nfdump
is located elsewhere, set it with

!!! setting "external/binaries"
    ```bash
    lnms config:set nfdump /usr/local/bin/nfdump
```

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_last_max 153600
    lnms config:set nfsen_top_max 500
    lnms config:set nfsen_top_N '[10, 20, 50, 100, 200, 500]'
    lnms config:set nfsen_top_default 20
    lnms config:set nfsen_stat_default srcip
    lnms config:set nfsen_order_default packets
    lnms config:set nfsen_last_default 900
    lnms config:set nfsen_lasts "{'300':'5 minutes', '600':'10 minutes', '900':'15 minutes', '1800':'30 minutes', '3600':'1 hour', '9600':'3 hours', '38400':'12 hours', '76800':'24 hours', '115200':'36 hours', '153600':'48 hours'}"
    ```

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_last_max 153600
    ```

The above is the max value in seconds one may pull stats for. The
higher this is, the more CPU and disk intensive the search will be.

Numbers larger than this will be set to this.

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_top_max 500
    ```

The above is max number of items to be displayed.

Numbers larger than this will be set to this.

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_top_N '[10, 20, 50, 100, 200, 500]'
    ```

The above is a array containing a list for the drop down menu how many
top items should be returned.

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_top_default 20
    ```

The above sets default top number to use from the drop down.

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_stat_default srcip
    ```

The above sets default stat type to use from the drop down.

```
record   Flow Records
ip       Any IP Address
srcip    SRC IP Address
dstip    DST IP Address
port     Any Port
srcport  SRC Port
dstport  DST Port
srctos   SRC TOS
dsttos   DST TOS
tos      TOS
as       AS
srcas    SRC AS
dstas    DST AS
```

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_order_default packets
    ```

The above sets default order type to use from the drop down. Any of
the following below are currently supported.

```
flows    Number of total flows for the time period.
packet   Number of total packets for the time period.
bytes    Number of total bytes for the time period.
pps      Packets Per Second
bps      Bytes Per Second
bpp      Bytes Per Packet
```

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_last_default 900
    ```

The above is the last default to use from the drop down.

!!! setting "external/nfsen"
    ```bash
    lnms config:set nfsen_lasts "{'300':'5 minutes', '600':'10 minutes', '900':'15 minutes', '1800':'30 minutes', '3600':'1 hour', '9600':'3 hours', '38400':'12 hours', '76800':'24 hours', '115200':'36 hours', '153600':'48 hours'}"
    ```

The above associative array contains time intervals for how
far back to go. The keys are the length in seconds and the
value is just a description to display.

