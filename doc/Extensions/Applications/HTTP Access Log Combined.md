## HTTP Access Log Combined

### SNMP Extend

1. Download the script onto the desired host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/http_access_log_combined -O /etc/snmp/http_access_log_combined
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/http_access_log_combined
    ```

3. Install the depends

    === "FreeBSD"
        ```bash
        pkg install p5-File-Slurp p5-MIME-Base64 p5-JSON p5-Statistics-Lite p5-File-ReadBackwards
        ```

    === "Debian/Ubuntu"
        ```bash
        apt-get install libfile-slurp-perl libmime-base64-perl libjson-perl libstatistics-lite-perl libfile-readbackwards-perl
        ```

4. Configure it if neeeded. Uses
   `/usr/local/etc/http_access_log_combined_extend.json`, unless
   specified via `-c`. See further below for configuration
   information.

5.  If on large setups where it won't complete in a timely manner, run it via cron.

    === "If not using cron"

    === "If using cron"
        Add the following to `/etc/crontab.d/librenms_http_access_log_combined`:

        ```bash
        */5 * * * * root /etc/snmp/http_access_log_combined -b -q -w
        ```

6.  Add one of the following to `/etc/snmp/snmpd.conf`.

    === "If not using cron"

        ```bash
        extend http_access_log_combined /etc/snmp/http_access_log_combined -b
        ```

    === "If using cron"

        ```bash
        extend http_access_log_combined cat /var/cache/http_access_log_combined.json.snmp
        ```

7. Either manually enable it for the device, rediscover the device, or
   wait for it to be rediscovered.

| Key               | Type         | Description                                                                                                                              |
|-------------------|--------------|------------------------------------------------------------------------------------------------------------------------------------------|
| access            | hash         | A hash of access logs to monitor. The key is the reporting name while the value is the path to it.                                       |
| error             | hash         | A hash of errors logs to monitor. The key is the reporting name while the value is the path to it. Must have a matching entry in access  |
| auto              | boolean, 0/1 | If auto mode should be used or not. If not defined and .access is not defined, then it will default to 1. Other wise it is undef, false. |
| auto_dir          | string       | The dir to look for files in. Default: `/var/log/apache/`                                                                                |
| auto_end_regex    | string       | What to match files ending in. Default: `.log$`                                                                                          |
| auto_access_regex | string       | What will be prepended to the end regexp for looking for access log files. Default: `-access`                                            |
| auto_error_regex  | string       | What will be prepended to the end regexp for looking for error log files. Default: `-error`                                              |

Auto will attempt to generate a list of log files to process. Will
look under the directory specified for files matching the built
regexp. The regexp is built by joining the access/error regexps to the
end regexp. so for access it would be come `-access.log$`.

The default auto config would look like below.

```JSON
{
    "auto": 1,
    "auto_dir": "/var/log/apache/",
    "auto_end_regex": ".log$",
    "auto_access_regex": "-access",
    "auto_error_regex": "-error"
}
```

So lets say the log dir, `/some/dir` in our case, has the following files.

```
foo:80-access.log
foo:80-error.log
foo:443-access.log
foo:443-error.log
bar-access.log
```

Then the auto generated stuff would be a like below.

```JSON
{
    "access":{
        "foo:80": "/some/dir/foo:80-access.log",
        "foo:443": "/some/dir/foo:443-access.log",
        "bar": "/some/dir/bar-access.log",
    },
    "error":{
        "foo:80": "/some/dir/foo:80-error.log",
        "foo:443": "/some/dir/foo:443-error.log",
    }
}
```

A manual config would be like below. Note that only `foo` has a error
log that the size will be checked for and reported via the stat
`error_size`.

```JSON
{
    "auto": 0,
    "access":{
        "foo":"/var/log/www/foo.log",
        "bar:80":"/var/log/www/bar:80.log"
        "bar:443":"/var/log/www/bar:443.log"
    },
    "error":{
        "foo":"/var/log/www/foo-error.log"
    }
}
```

8. (Optional) If you have SELinux in Enforcing mode, you must add a module so the script can open and read the httpd log files:

```bash
cat << EOF > snmpd_http_access_log_combined.te
module snmp_http_access_log_combined 1.0;

require {
        type httpd_log_t;
        type snmpd_t;
        class file { open read };
}

#============= snmpd_t ==============

allow snmpd_t httpd_log_t:file { open read };

EOF
checkmodule -M -m -o snmpd_http_access_log_combined.mod snmpd_http_access_log_combined.te
semodule_package -o snmpd_http_access_log_combined.pp -m snmpd_http_access_log_combined.mod
semodule -i snmpd_http_access_log_combined.pp
```