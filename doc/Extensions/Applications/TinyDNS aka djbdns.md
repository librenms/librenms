
## TinyDNS aka djbdns

### Agent

[Install the agent](../Agent-Setup.md) on this device if it isn't already
and copy the `tinydns` script to `/usr/lib/check_mk_agent/local/`

!!! note 
    We assume that you use DJB's [Daemontools](http://cr.yp.to/daemontools.html) to start/stop tinydns. And that your tinydns instance is located in `/service/dns`, adjust this path if necessary.

1. Replace your _log_'s `run` file, typically located in
   `/service/dns/log/run` with:

    ```bash
    #!/bin/sh
    exec setuidgid dnslog tinystats ./main/tinystats/ multilog t n3 s250000 ./main/
    ```

2. Create tinystats directory and chown:

    ```bash
    mkdir /service/dns/log/main/tinystats
    chown dnslog:nofiles /service/dns/log/main/tinystats
    ```

3. Restart TinyDNS and Daemontools: `/etc/init.d/svscan restart`
   
!!! note 
    Some say `svc -t /service/dns` is enough, on my install (Gentoo) it doesn't rehook the logging and I'm forced to restart it entirely.
