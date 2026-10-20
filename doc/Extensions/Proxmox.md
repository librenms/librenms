# Proxmox

LibreNMS discovers the virtual machines of Proxmox VE hosts and polls
their CPU, memory, disk, disk IO and network usage with the Proxmox VE API.

The host is detected with SNMP like any other device, the Proxmox API
polling method is added to it to collect the virtual machines.

## Create an API token

On the Proxmox host, create a user and an API token with read only access.
The `PVEAuditor` role is enough.

```bash
pveum user add librenms@pve
pveum aclmod / -user librenms@pve -role PVEAuditor
pveum user token add librenms@pve monitoring --privsep 0
```

Note the token ID (`librenms@pve!monitoring`) and the secret value, the
secret is only shown once.

Disk usage is read from the QEMU guest agent when it runs in the virtual
machine, otherwise the hypervisor values are used.

## Add the polling method

On the device, go to Edit -> Polling, add the **Proxmox API** method and
create a secret with the token ID and the token secret.

| Setting                | Default            |
|------------------------|--------------------|
| Hostname               | device's hostname  |
| Port                   | 8006               |
| Timeout                | 10 seconds         |
| Verify TLS Certificate | No                 |

Proxmox VE uses a self-signed certificate by default. Set Verify TLS
Certificate to Yes if the host has a trusted certificate.

The virtual machines are discovered by the `vminfo` module during the next
discovery.
