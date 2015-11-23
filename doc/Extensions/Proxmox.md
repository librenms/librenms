# Proxmox graphing
It is possible to create graphs of the Proxmox **VMs** that run on your monitored machines. Currently, only trafficgraphs are created. One for each interface on each VM. Possibly, IO grahps will be added later on.

The ultimate goal is to be able to create traffic bills for VMs, no matter on which physical machine that VM runs.

### Enabling Proxmox graphs

To enable Proxmox graphs, do the following:

In config.php, enable Proxmox:
```php
$config['enable_proxmox'] = 1;
```

Then, install [librenms-agent](http://docs.librenms.org/Extensions/Agent-Setup/) on the machines running Proxmox and enable the Proxmox-script using:

```bash
cp /opt/librenms-agent/proxmox /usr/lib/check_mk_agent/local/proxmox
chmod +x /usr/lib/check_mk_agent/local/proxmox
```

Then, restart the xinetd service
```bash
/etc/init.d/xinetd restart
```

Then in LibreNMS active the librenms-agent and proxmox application flag for the device you are monitoring.
You should now see an application in LibreNMS, as well as a new menu-item in the topmenu, allowing you to choose which cluster you want to look at.
