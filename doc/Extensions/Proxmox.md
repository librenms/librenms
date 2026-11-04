# Proxmox

LibreNMS discovers the virtual machines and LXC containers of Proxmox VE
hosts and polls their CPU, memory, disk, disk IO and network usage with the
Proxmox VE API. Disk operations are only available for virtual machines.

Add the Proxmox API polling method to **every node** of a cluster, each node
reports the guests it runs. The guests of a node without the method are not
monitored.

A guest shows under the device of the node it runs on. When it migrates, the
new node takes it over at its next poll and logs a migrated event. The guest
keeps its page and its graphs.

The graphs of a guest are stored per cluster instead of per device, in
`rrd/proxmox-<cluster id>/`. The cluster id comes from the fingerprint of the
Proxmox root CA, so clusters with the same name stay apart. Regenerating the
root CA starts new graphs, rename the directory to keep the old ones.

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
token stays read-only and can use the guest agent.

On Proxmox VE 8 and earlier, `PVEAuditor` does not grant guest agent access.
The guest OS is not collected and disk usage uses the hypervisor values.

Collecting them on these versions requires `VM.Monitor`, but that privilege
also allows guest-changing agent operations, such as running commands, writing
files and setting passwords. The token is then no longer read-only, so only
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
| Verify TLS Certificate | Yes                |

Proxmox VE uses a self-signed certificate by default. Install a trusted
certificate on the host, or set Verify TLS Certificate to No.

The Proxmox API method enables the `vminfo` module for the device, the
virtual machines are discovered during the next discovery. A device setting
in Edit -> Modules overrides it.
