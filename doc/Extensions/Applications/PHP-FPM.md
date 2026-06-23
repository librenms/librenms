# PHP-FPM

A small shell script that reports the status of PHP-FPM (FastCGI Process Manager).

### Install prereqs

=== "Debian/Ubuntu"

    ```bash
    apt-get install libfile-slurp-perl libjson-perl libstring-shellquote-perl libmime-base64-perl
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-File-Slurp p5-JSON p5-String-ShellQuote p5-MIME-Base64
    ```

=== "Fedora"

    ```bash
    dnf install perl-JSON perl-File-Slurp perl-String-ShellQuote
    ```

## PHP-FPM status page preparation

In order to use the status page, it must be activated (at least on Debian).
(Original code from https://www.tecmint.com/enable-monitor-php-fpm-status-in-nginx/)

    1. uncomment and edit the according options in your pool.conf.
    
        ´´´
        pm.status_path = /www-fpm-status
        pm.status_listen = 127.0.0.1:9001
        ping.path = /www-ping (optional)
        ```
    2.  add the status page to your webserver config (here nginx).
        ´´´nginx
        location ~ ^/(www-fpm-status|www-ping)$ {
            allow 127.0.0.1;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_pass 127.0.0.1:9001;
            #fastcgi_pass   unix:/var/run/php/php8.4-fpm.sock;
        }
        ```
    3. restart nginx and PHP-FPM
         ```bash
        sudo systemctl reload nginx
        sudo systemctl restart php8.4-fpm
        ```

## Agent or SNMP Extend

=== "SNMP Extend"

    1. Copy the shell script, phpfpmsp, to the desired host

        ```bash
        wget https://github.com/librenms/librenms-agent/raw/master/snmp/php-fpm -O /etc/snmp/php-fpm
        ```

    2. Make the script executable

        ```bash
        chmod +x /etc/snmp/php-fpm
        ```

    3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:
    ```
    extend phpfpmsp /etc/snmp/php-fpm
    ```

    4. Create the config file
    `/usr/local/etc/php-fpm_extend.json`. Alternate locations may be
    specified using the the `-f` switch. Akin to like below. For more
    information, see `/etc/snmp/php-fpm --help`.

        ```json
        {
        "pools":{
                "www": "http://localhost/www-fpm-status",
                "thefrog": "https://thefrog/fpm-status",
                "foobar": "https://foo.bar/fpm-status"
            }
        }
        ```

    6. Restart snmpd on the host

        ```bash
        sudo systemctl restart snmpd
        ```

        The application should be auto-discovered as described at the top of the page. If it is not, please follow the steps set out under `SNMP Extend` heading top of page.

=== "Agent"

    [Install the agent](../Agent-Setup.md) on this device if it isn't already
    and copy the `phpfpmsp` script to `/usr/lib/check_mk_agent/local/`
