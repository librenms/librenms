# Proxmox

LibreNMS discovers the virtual machines and LXC containers of Proxmox VE
hosts and polls their CPU, memory, disk, disk IO and network usage with the
Proxmox VE API. Disk operations are only available for virtual machines.

Add the Proxmox API polling method to the device.

## Create an API token

On the Proxmox host, create a user and an API token with the `PVEAuditor`
role.

```bash
pveum user add librenms@pve
pveum aclmod / -user librenms@pve -role PVEAuditor
pveum user token add librenms@pve monitoring --privsep 0
```

Note the token ID (`librenms@pve!monitoring`) and the secret value, the
secret is only shown once.

Disk usage of a virtual machine is read from the QEMU guest agent when it
runs in the virtual machine, otherwise the hypervisor values are used. The
guest OS of a virtual machine also comes from the agent. Hypervisor values
report no used space for virtual machines and the size of the boot disk only.

### Guest agent access

On Proxmox VE 9 and later, `PVEAuditor` includes `VM.GuestAgent.Audit`, so the
token stays read only and can use the guest agent.

On Proxmox VE 8 and earlier, `PVEAuditor` does not grant guest agent access.
The guest OS is not collected and disk usage uses the hypervisor values.

Collecting them on these versions requires `VM.Monitor`, but that privilege
also allows guest-changing agent operations, such as running commands, writing
files and setting passwords. The token is then no longer read only, so only
grant it if you accept that scope:

```bash
pveum role add LibreNMSGuestAgent --privs VM.Monitor
pveum aclmod /vms -user librenms@pve -role LibreNMSGuestAgent
```

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

The Proxmox API method enables the `vminfo` module for the device, the
virtual machines are discovered during the next discovery. A device setting
in Edit -> Modules overrides it.
