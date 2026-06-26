---
title: Sub-Directory Installation
description: How to run LibreNMS under a subdirectory (e.g. example.com/librenms/) using nginx or Apache.
---

# Sub-Directory Installation

By default LibreNMS expects to be the only application at the root of a
domain (e.g. `http://example.com/`). If you need to run it alongside other
applications on the same host, you have two options:

- **Subdirectory** (this page) - serve LibreNMS at a path on an existing domain
  (e.g. `http://example.com/librenms/`). The web server must route requests for
  that path to the LibreNMS document root, and PHP must be told the subdirectory
  prefix so that generated URLs are correct.
- **Subdomain** - give LibreNMS its own hostname (e.g. `http://librenms.example.com/`).
  No extra configuration is needed beyond a standard virtual host pointing at the
  LibreNMS document root.

=== "nginx"

    See also the [nginx `alias` documentation](https://nginx.org/en/docs/http/ngx_http_core_module.html#alias)
    and the [ngx_http_fastcgi_module reference](https://nginx.org/en/docs/http/ngx_http_fastcgi_module.html)
    for details on `fastcgi_param` and `fastcgi_params`.

    Add a location block for the subdirectory inside your server block:

    ``` nginx title="nginx subdirectory configuration"
    server {
        listen 80;
        server_name example.com;

        location = /librenms { return 301 /librenms/; }

        location /librenms/ {
            alias /opt/librenms/html/;
            index index.php;
            try_files $uri $uri/ @librenms;

            location ~ \.php$ {
                fastcgi_pass unix:/run/php-fpm-librenms.sock;
                fastcgi_index index.php;
                fastcgi_param SCRIPT_FILENAME $request_filename;
                include fastcgi_params;
            }
        }

        location @librenms {
            rewrite ^/librenms/(.*)$ /librenms/index.php?$1 last;
        }
    }
    ```

    - `alias` maps `/librenms/` URLs to the LibreNMS `html/` document root on disk.
    - `fastcgi_pass` - adjust to your PHP-FPM socket path (check `/etc/php/*/fpm/pool.d/`).
    - `SCRIPT_FILENAME` is required with `alias` to map the request URI to the correct file path on disk.
    - `include fastcgi_params` sets `SCRIPT_NAME` (e.g. `/librenms/index.php`), which LibreNMS uses to auto-detect the subdirectory prefix.
    - `@librenms` - fallback named location that passes unmatched paths as a query string to `index.php` for Laravel routing.

=== "Apache"

    See also the [Apache `Alias` documentation](https://httpd.apache.org/docs/current/mod/mod_alias.html#alias)
    and the [mod_rewrite `RewriteBase` reference](https://httpd.apache.org/docs/current/mod/mod_rewrite.html#rewritebase).

    Required modules: `mod_rewrite`, `mod_alias`, `mod_proxy`, `mod_proxy_fcgi`.

    ``` apache title="Apache subdirectory configuration"
    <VirtualHost *:80>
        ServerName example.com

        AllowEncodedSlashes NoDecode

        RedirectMatch ^/$ /librenms/
        RedirectMatch ^/librenms$ /librenms/

        Alias /librenms /opt/librenms/html

        <Directory /opt/librenms/html>
            Options FollowSymLinks MultiViews
            AllowOverride All
            Require all granted
        </Directory>

        <FilesMatch \.php$>
            SetHandler "proxy:unix:/run/php-fpm-librenms.sock|fcgi://localhost"
        </FilesMatch>

        <FilesMatch "^\.">
            Require all denied
        </FilesMatch>
    </VirtualHost>
    ```

    **1. Add the VirtualHost config** to `/etc/apache2/sites-available/librenms.conf`
    (Debian/Ubuntu) or `/etc/httpd/conf.d/librenms.conf` (RHEL/CentOS), then enable it:

    ```
    a2ensite librenms.conf
    ```

    **2. Update `/opt/librenms/html/.htaccess`** - change `RewriteBase /` to match the subdirectory:

    ```
    RewriteBase /librenms
    ```

    This is required for Apache to route incoming requests to `index.php` correctly.
    Without it, rewrites resolve to `/index.php` instead of `/librenms/index.php`
    and requests never reach LibreNMS. With it set correctly, Apache also passes the
    right `SCRIPT_NAME` to PHP so LibreNMS detects the subdirectory automatically.

    **3. Restart Apache:**

    ```
    systemctl restart apache2    # Debian/Ubuntu
    systemctl restart httpd      # RHEL/CentOS
    ```

!!! info "How subdirectory detection works"
    LibreNMS reads `SCRIPT_NAME` set by the web server to detect the subdirectory
    prefix automatically. How it gets the correct value differs per web server:

    - **nginx** - `include fastcgi_params` sets it from `$fastcgi_script_name`, derived
      from the request URI after the `@librenms` rewrite. No extra config needed.
    - **Apache** - `RewriteBase /librenms` in `.htaccess` ensures rewrites produce the
      correct URI, which Apache then includes in `SCRIPT_NAME`.

??? info "SCRIPT_FILENAME vs SCRIPT_NAME"
    These are two distinct variables that serve different purposes:

    | Variable | Value (example) | Used by |
    | --- | --- | --- |
    | `SCRIPT_FILENAME` | `/opt/librenms/html/index.php` | PHP-FPM: filesystem path of the file to execute |
    | `SCRIPT_NAME` | `/librenms/index.php` | PHP app: URL path of the script, used for base-path detection |

    `SCRIPT_FILENAME` must be set explicitly in the nginx config because `alias`
    translates the URL path to a different filesystem path that nginx cannot derive
    without being told. Apache resolves this via the `Alias` directive instead.

    `SCRIPT_NAME` is set automatically by both web servers based on the request URI
    after rewriting. Laravel and Symfony read it to strip the subdirectory prefix
    when parsing incoming URLs.

!!! tip "APP_URL is optional"
    When the web server is configured correctly, LibreNMS detects the subdirectory
    prefix from `SCRIPT_NAME` automatically. Set `APP_URL` only to pin the scheme
    and hostname, or as a fallback behind reverse proxies where `SCRIPT_NAME` may
    not be reliable:

    ```
    APP_URL=http://example.com/librenms
    ```

    After setting it, run `php artisan config:cache`.
