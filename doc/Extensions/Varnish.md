# Setting up Varnish 

This document will explain how to setup Varnish for LibreNMS.

### Varnish installation
This example is based on a fresh LibreNMS install, on a minimimal CentOS installation.
In this example, we'll use the default package available through yum.

- Install Varnish

```ssh
yum install varnish
chkconfig varnish on
```
- Confirm that Varnish has been installed 

```ssh
varnishd -V
varnishd (varnish-2.1.5 SVN )
```
- Change the webservers port to 8080, since we'll put Varnish in front(Or whatever you prefer) 

- Point Varnish towards the webserver by editing the default.vcl 

```ssh
vi /etc/varnish/default.vcl

backend default {
  .host = "127.0.0.1";
  .port = "8080";
}

```
- Change the default port Varnish listens on

```ssh
vi /etc/sysconfig/varnish

VARNISH_LISTEN_PORT=80
```

- Restart webserver(Apache in this case) and start Varnish afterwards

```ssh
service httpd restart
service varnish start
```

- Browse around the webui to build up the cache, verify that the cache is working afterwards

```ssh
varnishstat
```
