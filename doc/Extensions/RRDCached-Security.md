### Securing with nginx
According to the [man page](https://linux.die.net/man/1/rrdcached), under "SECURITY CONSIDERATIONS", rrdcached has no authentication or security except for running under a unix socket. To secure your rrdcached installation, you can proxy it using nginx to allow only specific IPs to connect.

using the same setup above, using nginx version 1.9.0 or later, you can follow this setup to proxy the default rrdcached port to the local unix socket.

(You can use `./conf.d` for your configuration as well)

`mkdir /etc/nginx/streams-{available,enabled}`

add the following to your nginx.conf file:
```nginx
#/etc/nginx/nginx.conf
...
stream {
    include /etc/nginx/streams-enabled/*;
}
```

add this to `/etc/nginx/streams-available/rrd`
```nginx
server {
    listen 42217;

    error_log  /var/log/nginx/rrd.stream.error.log;

    allow $LibreNMS_IP;
    deny all;

    proxy_pass unix:/var/run/rrdcached/rrdcached.sock;
}
```
replace `$LibreNMS_IP` with the ip of the server that will be using rrdcached. You can specify more than one `allow` statement.
This will bind nginx to TCP 42217 (the default rrdcached port), allow the specified IPs to connect, and deny all others.

next, we'll symlink the config to streams-enabled:
`ln -s /etc/nginx/streams-{available,enabled}/rrd`

and reload nginx
`service nginx reload`
