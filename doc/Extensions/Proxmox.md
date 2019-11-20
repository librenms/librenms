source: Extensions/Proxmox.md
path: blob/master/doc/

# Proxmox graphing

It is possible to create graphs of the Proxmox **VMs** that run on
your monitored machines. Currently, only traffic graphs are
created. One for each interface on each VM. Possibly, IO graphs will
be added later on.

The ultimate goal is to be able to create traffic bills for VMs, no
matter on which physical machine that VM runs.

# Enabling Proxmox graphs

To enable Proxmox graphs, do the following:

In config.php, enable Proxmox:

```php
$config['enable_proxmox'] = 1;
```

Then, install git and
[librenms-agent](http://docs.librenms.org/Extensions/Applications/) on
the machines running Proxmox and enable the Proxmox-script using:

```bash
cp /opt/librenms-agent/agent-local/proxmox /usr/lib/check_mk_agent/local/proxmox
chmod +x /usr/lib/check_mk_agent/local/proxmox
```

Then, enable and start the check_mk service using systemd

```bash
cp /opt/librenms-agent/check_mk@.service /opt/librenms-agent/check_mk.socket /etc/systemd/system
systemctl daemon-reload
systemctl enable check_mk.socket && systemctl start check_mk.socket
```

Then in LibreNMS active the librenms-agent and proxmox application
flag for the device you are monitoring. You should now see an
application in LibreNMS, as well as a new menu-item in the topmenu,
allowing you to choose which cluster you want to look at.

# Note, if you want to use use xinetd instead of systemd

Its possible to use the librenms-agent started by xinetd instead of
systemd. One use case is if you are forced to use a old Proxmox
installation. After installing the librenms-agent (see above) please
copy enable the xinetd config, then restart the xinetd service:

```bash
cp check_mk_xinetd /etc/xinetd.d/check_mk
/etc/init.d/xinetd restart
```
