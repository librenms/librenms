source: Extensions/Varnish.md
path: blob/master/doc/

# Varnish Installation Guide

This document explains how to install Varnish Reverse Proxy for LibreNMS.

Varnish is caching software that sits logically between an HTTP client
and an HTTP server. Varnish caches HTTP responses from the HTTP
server. If an HTTP request can not be responded to by the Varnish
cache it directs the request to the HTTP Server. This type of HTTP
caching is called a reverse proxy server. Caching your HTTP server can
decrease page load times significantly.

# Simplified block diagram of an Apache HTTP server with Varnish 4.0 Reverse Proxy

![Block Diagram 1](/img/varnish_block.png)

# CentOS 7 Varnish Installation

In this example we will assume your Apache 2.4.X HTTP server is working and
configured to process HTTP requests on port 80.  If not, please see
[Installing LibreNMS](../Installation/Installation-CentOS-7-Apache.md)

# Install Varnish 4.0 RPM

- Enable the Varnish CentOS 7 repo and install

```bash
rpm --nosignature -i https://repo.varnish-cache.org/redhat/varnish-4.0.el7.rpm
yum install varnish
```

By default Varnish listens for HTTP requests on port 6081.

- Temporarily add a firewalld rule for testing Varnish.

```bash
firewall-cmd --zone=public --add-port=6081/tcp
```

# Test Varnish

- Start Varnish

```bash
systemctl start varnish
```

Using a web browser navigate to <server ip addr>:6081 or
127.0.0.1:6081. You should see a Varnish error message, this shows
that Varnish is working. Example error message:

```ssh
Error 503 Backend fetch failed

Backend fetch failed

Guru Meditation:

XID: 3

Varnish cache server

```

# Edit Varnish Parameters

Now we need to configure Varnish to listen to HTTP requests on port 80 and
relay those requests to the Apache HTTP server on port 8080 (see block diagram).

- Stop Varnish.

```bash
systemctl stop varnish
```

- Create a back-up of varnish.params just in case you make a mistake.

```bash
cp /etc/varnish/varnish.params /etc/varnish/varnish.params.bak
```

- Edit the varnish.params config.

```bash
vim /etc/varnish/varnish.params
```

Set the VCL location, IP address, port, and cache location and
size. `malloc` sets the cache location to RAM, and `512M` sets the
cache size to 512MB.

```vcl
VARNISH_LISTEN_ADDRESS=192.168.1.10
VARNISH_LISTEN_PORT=80
VARNISH_VCL_CONF=/etc/varnish/librenms.vcl
VARNISH_STORAGE="malloc,512M"
```

Example varnish.params:

```vcl
# Set this to 1 to make systemd reload try to switch vcl without restart.
RELOAD_VCL=1

# Main configuration file. You probably want to change it.
VARNISH_VCL_CONF=/etc/varnish/librenms.vcl

# Default address and port to bind to. Blank address means all IPv4
# and IPv6 interfaces, otherwise specify a host name, an IPv4 dotted
# quad, or an IPv6 address in brackets.
VARNISH_LISTEN_ADDRESS=192.168.1.10
VARNISH_LISTEN_PORT=80

# Admin interface listen address and port
VARNISH_ADMIN_LISTEN_ADDRESS=127.0.0.1
VARNISH_ADMIN_LISTEN_PORT=6082

# Shared secret file for admin interface
VARNISH_SECRET_FILE=/etc/varnish/secret

# Backend storage specification, see Storage Types in the varnishd(5)
# man page for details.
VARNISH_STORAGE="malloc,512M"

# Default TTL used when the backend does not specify one
VARNISH_TTL=120

# User and group for the varnishd worker processes
VARNISH_USER=varnish
VARNISH_GROUP=varnish

# Other options, see the man page varnishd(1)
DAEMON_OPTS="-p thread_pool_min=5 -p thread_pool_max=500 -p thread_pool_timeout=300"
```

# Configure Apache for Varnish

Edit librenms.conf and modify the Apache Virtual Host listening port.

- Modify: `<VirtualHost *:80>` to `<VirtualHost *:8080>`

```bash
vim /etc/httpd/conf.d/librenms.conf
```

Varnish can not share a port with Apache. Change the Apache listening port to 8080.

- Modify: `Listen 80` to `Listen 8080`

```bash
vim /etc/httpd/conf/httpd.conf
```

- Create the librenms.vcl

```bash
cd /etc/varnish
touch librenms.vcl
```

- Set ownership and permissions for Varnish files.

```bash
chown varnish:varnish default.vcl varnish.params secret
chmod 644 default.vcl varnish.params secret
```

Edit the librenms.vcl.

```bash
vim librenms.vcl
```

Paste example VCL config, read config comments for more information.

```vcl
#
# This is an example VCL file for Varnish.
#
# It does not do anything by default, delegating control to the
# builtin VCL. The builtin VCL is called when there is no explicit
# return statement.
#
# See the VCL chapters in the Users Guide at https://www.varnish-cache.org/docs/
# and http://varnish-cache.org/trac/wiki/VCLExamples for more examples.

# Marker to tell the VCL compiler that this VCL has been adapted to the
# new 4.0 format.
vcl 4.0;

# Default backend definition. Set this to point to your Apache server.
backend librenms {
    .host = "127.0.0.1";
    .port = "8080";
}

# In this example our objective is to cache static content with Varnish and temporarily
# cache dynamic content in the client web browser.

sub vcl_recv {
    # HTTP requests from client web browser.
    # Here we remove any cookie HTTP requests for the 'librenms.domain.net' host
    # containing the matching file extensions. We don't have to match by host if you
    # only have LibreNMS running on Apache.
    # If the cookies are not removed from the HTTP request then Varnish will not cache
    # the files. 'else' function is set to 'pass', or don't cache anything that doesn't
    # match.

    if (req.http.host ~ "^librenms.domain.net") {
        set req.backend_hint = librenms;
        if (req.url ~ "\.(png|gif|jpg|jpeg|ico|pdf|js|css|svg|eot|otf|woff|woff2|ttf)$") {
            unset req.http.Cookie;
        }

        else{
            return(pass);
        }
    }
}

sub vcl_backend_response {
    # 'sub vcl_backend_response' is the same function as 'sub vcl_fetch' in Varnish 3, however,
    # the syntax is slightly different
    # This function happens after we read the response headers from the backend (Apache).
    # First function 'if (bereq.url ~ "\' removes cookies from the Apache HTTP responses
    # that match the file extensions that are between the quotes, and cache the files for 24 hours.
    # This assumes you update LibreNMS once a day, otherwise restart Varnish to clear cache.
    # Second function 'if (bereq.url ~ "^/' removes the Pragma no-cache statements and sets the age
    # of how long the client browser will cache the matching urls.
    # LibreNMS graphs are updated every 300 seconds, 'max-age=300' is set to match this behavior.
    # We could cache these URLs in Varnish but it would add to the complexity of the config.

    if (bereq.http.host ~ "^librenms.domain.net") {
        if (bereq.url ~ "\.(png|gif|jpg|jpeg|ico|pdf|js|css|svg|eot|otf|woff|woff2|ttf)$") {
            unset beresp.http.Set-cookie;
            set beresp.ttl = 24h;
        }

        if (bereq.url ~ "^/graph.php" || "^/device/" || "^/iftype/" || "^/customers/" || "^/health/" || "^/apps/" || "^/(plugin)$" || "^/(alert)$" || "^/eventlog/" || "^/graphs/" || "^/ports/" ) {
            unset beresp.http.Pragma;
            set beresp.http.Cache-Control = "max-age=300";
        }
    }
}

sub vcl_deliver {
    # Happens when we have all the pieces we need, and are about to send the
    # response to the client.
    # You can do accounting or modifying the final object here.

    return (deliver);
}
```

- Reload rules to remove the temporary port rule we added earlier.

```bash
firewall-cmd --reload
```

Varnish caching does not take effect immediately.  You will need to
browse the LibreNMS website to build up the cache.

Use the command `varnishstat` to monitor Varnish caching.  Over time
you should see 'MAIN.cache_hit' and 'MAIN.client_req' increase.  With
the above VCL the hit to request ratio is approximately 84%.

- Session based VCL (coming soon)

- Testing and debugging VCL (coming soon)
