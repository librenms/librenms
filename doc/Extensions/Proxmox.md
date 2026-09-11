# Proxmox graphing

LibreNMS creates graphs of the Proxmox **VMs** on your monitored
machines. It creates only traffic graphs, one for each interface of
each VM. IO graphs can come later.

The final goal is a traffic bill for each VM, on any physical machine.

## Enabling Proxmox graphs

To enable the Proxmox graphs, do these steps:

In `config.php`, enable Proxmox:

```php
$config['enable_proxmox'] = 1;
```

Then install git and [librenms-agent](Applications.md) on the Proxmox
machines. Then enable the Proxmox script:

```bash
cp /opt/librenms-agent/agent-local/proxmox /usr/lib/check_mk_agent/local/proxmox
chmod +x /usr/lib/check_mk_agent/local/proxmox
```

Then enable and start the check_mk service with systemd:

```bash
cp /opt/librenms-agent/check_mk@.service /opt/librenms-agent/check_mk.socket /etc/systemd/system
systemctl daemon-reload
systemctl enable check_mk.socket && systemctl start check_mk.socket
```

Then enable the librenms-agent flag and the proxmox application flag on
the monitored device in LibreNMS. The application then appears in
LibreNMS. A new item also appears in the top menu. This item selects
the cluster to view.

## Note, if you want to use use xinetd instead of systemd

xinetd can start the librenms-agent in place of systemd. One use case
is an old Proxmox installation. Install the librenms-agent, as above.
Then copy and enable the xinetd config. Then restart the xinetd
service:

```bash
cp check_mk_xinetd /etc/xinetd.d/check_mk
/etc/init.d/xinetd restart
```
