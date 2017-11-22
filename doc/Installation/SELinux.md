# Selinux

Various permissions are needed for LibreNMS.
All commands should be run as root.

## Folder Access

    semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/logs(/.*)?'
    semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/logs(/.*)?'
    restorecon -RFvv /opt/librenms/logs/
    semanage fcontext -a -t httpd_sys_content_t '/opt/librenms/rrd(/.*)?'
    semanage fcontext -a -t httpd_sys_rw_content_t '/opt/librenms/rrd(/.*)?'
    restorecon -RFvv /opt/librenms/rrd/
  
## Allow sending email

    setsebool -P httpd_can_sendmail=1
    
## Allow PHP-FPM to run scripts
This is only needed if using PHP-FPM.  Nginx users are likely using PHP-FPM.

    setsebool -P httpd_execmem 1


## Allow fping
Needed for checking if devices are available when adding from the webui.

Create the file http_fping.tt with the following contents:
```
module http_fping 1.0;

require {
type httpd_t;
class capability net_raw;
class rawip_socket { getopt create setopt write read };
}

#============= httpd_t ==============
allow httpd_t self:capability net_raw;
allow httpd_t self:rawip_socket { getopt create setopt write read };
```

Then run these commands

    checkmodule -M -m -o http_fping.mod http_fping.tt
    semodule_package -o http_fping.pp -m http_fping.mod
    semodule -i http_fping.pp
