## Text Debug

The purpose of this is to assist with debugging/triaging, especially
in cases where syslog-ng has a complex config will likely die before
snmpd does.

The output size for the SNMP return, which is the size of
`/var/cache/text_blob_extend/snmp`, is several dozen KB in
general. Best to use with TCP SNMP polling.

1. Copy the extend into place
```
wget https://github.com/librenms/librenms-agent/raw/master/snmp/text_blob -O /etc/snmp/text_blob
```

2. Make it executable.
```
chmod +x /etc/snmp/text_blob
```

3. Install the depends.
```
# FreeBSD
pkg install p5-JSON p5-File-Slurp p5-MIME-Base64
# Debian
apt-get install libjson-perl libmime-base64-perl libfile-slurp-perl
```

4. Configure cron.
```
*/5 * * * * /etc/snmp/text_blob -q
```


5. Then set it up in snmpd.conf
```
extend text_blob /bin/cat /var/cache/text_blob_extend/snmp
```

6. Configure the extend.

The default config is `/usr/local/etc/text_blob_extend.json`.

| JSON path    | Description                                                                                                         |
|--------------|---------------------------------------------------------------------------------------------------------------------|
| .blobs       | A hash of commands to run. The key values are the name of the blob.                                                 |
| .global_envs | A hash of enviromental values set.                                                                                  |
| .blob_envs   | A hash of per blob env values. The key name of the blob and each value is a sub hash of enviromental values to set. |
| .output_dir  | Output directory to use. Default is `/var/cache/text_blob_extend`.                                                  |

```json
{
  "global_envs":{
    "NO_COLOR": 1
  },
  "blobs":{
    "dmesg": "dmesg | tail -n 100",
    "top_io": "top -b -m io -j",
    "top_cpu": "top -b -m cpu -w -j",
    "routes": "ip r s",
    "ps axuw": "ps axuw",
    "netstat": "ncnetstat -n --pct 2> /dev/null"
  }
}
```
