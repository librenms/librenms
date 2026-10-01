To run LibreNMS in a subdirectory on your Apache server, put the
directives of the LibreNMS directory into the base server
configuration. You can also put them into a virtual host container. For
a virtual host, put the directives into the configuration file of that
virtual host. On an RHEL distribution such as CentOS, the base server
file is `/etc/httpd/conf.d/librenms.conf`. On a Debian distribution
such as Ubuntu, the file is `/etc/apache2/sites-available/default`.

```apache
#These directives can be inside a virtual host or in the base server configuration
AllowEncodedSlashes On
Alias /librenms /opt/librenms/html

<Directory "/opt/librenms/html">
    AllowOverride All
    Options FollowSymLinks MultiViews
</Directory>
```

Change the `RewriteBase` directive in `html/.htaccess` to your
subdirectory name. For LibreNMS at <http://example.com/librenms/>,
change `RewriteBase /` to `RewriteBase /librenms`.

Then set `APP_URL=/librenms/` in `.env`. Then run
`lnms config:set base_url '/librenms/'`.
