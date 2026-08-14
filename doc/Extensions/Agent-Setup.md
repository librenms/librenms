# Check_MK Setup

The agent collects data from remote systems. LibreNMS works with
check_mk, at [the librenms-agent
repository](https://github.com/librenms/librenms-agent). You can extend
the agent with data about the [applications](Applications.md) on the
remote system.

## Installation

### Linux / BSD

Install systemd or xinetd on the host of the agent.

The agent uses TCP port 6556. Permit access from the **LibreNMS host**.
With the [Distributed Polling](Distributed-Poller.md) setup, also
permit access from the **poller nodes**.

On each host with the agent, do these steps:

1: Clone the `librenms-agent` repository:

```bash
cd /opt/
git clone https://github.com/librenms/librenms-agent.git
cd librenms-agent
```

2: Copy the relevant check_mk_agent to `/usr/bin`:

| linux | freebsd |
| --- | --- |
| `cp check_mk_agent /usr/bin/check_mk_agent` | `cp check_mk_agent_freebsd /usr/bin/check_mk_agent` |

```bash
chmod +x /usr/bin/check_mk_agent
```

3: Copy the service file(s) into place.

| xinetd | systemd |
| --- | --- |
| `cp check_mk_xinetd /etc/xinetd.d/check_mk` | `cp check_mk@.service check_mk.socket /etc/systemd/system` |

4: Create the relevant directories.

```bash
mkdir -p /usr/lib/check_mk_agent/plugins /usr/lib/check_mk_agent/local
```

5: Copy each necessary script from `agent-local/` into
`/usr/lib/check_mk_agent/local`. The sections above give the full setup
instructions of each application.

6: Make each necessary script executable with
`chmod +x /usr/lib/check_mk_agent/local/$script`.

7: Enable the check_mk service

| xinetd | systemd |
| --- | --- |
| `/etc/init.d/xinetd restart` | `systemctl enable check_mk.socket && systemctl start check_mk.socket` |

8: Log in to the LibreNMS web interface and edit the monitored device.
In the modules section, enable unix-agent.

9: Then enable your applications under Applications.

10: Wait about 10 minutes. The data then appears in the graphs under
Apps for that device.

#### Restrict the devices on which the agent listens: Linux systemd
To limit the network adapter of the agent, do these steps:

1: Edit `/etc/systemd/system/check_mk.socket`

2: Under the `[Socket]` section, add a new line `BindToDevice=` and the name of your network adapter.

3: If systemd already holds the script, run `systemctl daemon-reload`.
Then run `systemctl restart check_mk.socket`.


### Windows
1. Get version 1.2.6b5 of the check_mk agent from the check_mk GitHub
   repository. Use the exe or msi file, or compile it yourself:
   <https://github.com/tribe29/checkmk/tree/v1.2.6b5/agents/windows>
2. Run the msi / exe
3. Your LibreNMS instance must reach TCP port 6556 on the target.
