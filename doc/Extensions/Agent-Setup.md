source: Extensions/Agent-Setup.md
path: blob/master/doc/

The agent can be used to gather data from remote systems you can use
LibreNMS in combination with check_mk (found
[here](https://github.com/librenms/librenms-agent)). The agent can be
extended to include data about [applications](Applications.md) on the
remote system.

Make sure that systemd or xinetd is installed on the host you want to
run the agent on.

The agent uses TCP-Port 6556, please allow access from the **LibreNMS
host** and **poller nodes** if you're using the [Distributed
Polling](http://docs.librenms.org/Extensions/Distributed-Poller/)
setup.

On each of the hosts you would like to use the agent on, you need to do the following:

1: Clone the `librenms-agent` repository:

```shell
cd /opt/
git clone https://github.com/librenms/librenms-agent.git
cd librenms-agent
```

2: Copy the relevant check_mk_agent to `/usr/bin`:

| linux | freebsd |
| --- | --- |
| `cp check_mk_agent /usr/bin/check_mk_agent` | `cp check_mk_agent_freebsd /usr/bin/check_mk_agent` |

```shell
chmod +x /usr/bin/check_mk_agent
```

3: Copy the service file(s) into place.

| xinetd | systemd |
| --- | --- |
| `cp check_mk_xinetd /etc/xinetd.d/check_mk` | `cp check_mk@.service check_mk.socket /etc/systemd/system` |

4: Create the relevant directories.

```shell
mkdir -p /usr/lib/check_mk_agent/plugins /usr/lib/check_mk_agent/local
```

5: Copy each of the scripts from `agent-local/` into
`/usr/lib/check_mk_agent/local` that you require to be graphed.  You
can find detail setup instructions for specific applications above.

6: Make each one executable that you want to use with `chmod +x
/usr/lib/check_mk_agent/local/$script`

7: Enable the check_mk service

| xinetd | systemd |
| --- | --- |
| `/etc/init.d/xinetd restart` | `systemctl enable check_mk.socket && systemctl start check_mk.socket` |

8: Login to the LibreNMS web interface and edit the device you want to
monitor. Under the modules section, ensure that unix-agent is enabled.

9: Then under Applications, enable the apps that you plan to monitor.

10: Wait for around 10 minutes and you should start seeing data in
your graphs under Apps for the device.

