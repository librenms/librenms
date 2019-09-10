source: Extensions/Sub-Directory.md
path: blob/master/doc/

To run LibreNMS under a subdirectory on your Apache server, the
directives for the LibreNMS directory are placed in the base server
configuration, or in a virtual host container of your choosing. If
using a virtual host, place the directives in the file where the
virtual host is configured. If using the base server on RHEL
distributions (CentOS, Scientific Linux, etc.) the directives can be
placed in `/etc/httpd/conf.d/librenms.conf`. For Debian distributions
(Ubuntu, etc.) place the directives in
`/etc/apache2/sites-available/default`.

```apache
#These directives can be inside a virtual host or in the base server configuration
AllowEncodedSlashes On
Alias /librenms /opt/librenms/html

<Directory "/opt/librenms/html">
    AllowOverride All
    Options FollowSymLinks MultiViews
</Directory>
```

The `RewriteBase` directive in `html/.htaccess` must be rewritten to
reference the subdirectory name. Assuming LibreNMS is running at
http://example.com/librenms/, you will need to change `RewriteBase /`
to `RewriteBase /librenms`.

Finally, set `APP_URL=/librenms/` in .env and `$config["base_url"] =
'/librenms/';` in config.php.
