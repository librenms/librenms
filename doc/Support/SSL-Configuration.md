source: Support/SSL-Configuration.md
path: blob/master/doc/

# Ubuntu 16.04

## Enabling HTTPS - Nginx

This example is specifically for Ubuntu 16.04 with Nginx and uses an
SSL certificate from Let's Encrypt. Please follow
[this](https://www.digitalocean.com/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-16-04)
excellent tutorial on setting up nginx with an SSL cert from LetsEncrypt.

### Step 1

Follow the LetsEncrypt tutorial until you get to *Step 2: Obtain and
SSL Certificate.* In this section you need to change some config in
the Nginx configuration. The tutorial directs you to edit
`/etc/nginx/sites-available/default` however in the standard LibreNMS
install, the file you need to edit is actually
`/etc/nginx/conf.d/librenms.conf`.

### Step 2

When you get to *Step 3: Configure TLS/SSL on Web Server (Nginx)*
there are some differences again. Follow the instructions regarding
the "snippet" configuration, the main differences come when you get to
*Adjust the Nginx Configuration to Use SSL*. Here you are again
directed to edit `/etc/nginx/sites-available/default`, so we need to
go to `/etc/nginx/conf.d/librenms.conf`.

`sudo vi /etc/nginx/sites-available/default`

The top section of this file will look like this:

```
server {
    listen         80;
    listen         [::]:80;
    server_name    example.com;
    root        /opt/librenms/html;
    index       index.php;
    access_log  /opt/librenms/logs/access_log;
    error_log   /opt/librenms/logs/error_log;
```

Edit it to look like this (obviously changing example.com to your actual domain name):

```
server {
    listen         80;
    listen         [::]:80;
    server_name    example.com;
    return         301 https://$server_name$request_uri;
}


server {
 listen              443 ssl http2;
 listen              [::]:443 ssl http2;
 include snippets/ssl-example.com.conf;
 include snippets/ssl-params.conf;
 server_name example.com;
 root        /opt/librenms/html;
 index       index.php;
 access_log  /opt/librenms/logs/access_log;
 error_log   /opt/librenms/logs/error_log;
```

This config will redirect HTTP traffic to HTTPS and use the SSL config
that you have just set up.

Check for syntax errors
`sudo nginx -t`

Then restart Nginx
`sudo systemctl restart nginx`

### Step 3

Follow the rest of the tutorial, especially if you have the server's firewall enabled.

# CentOS

## Enabling HTTPS - Apache

This example is specifically for CentOS 7 with Apache

Please follow [this](https://wiki.centos.org/HowTos/Https) How To from
CentOS wiki on setting up Apache with a SSL cert.

### Step 1

Follow the How to until you get to step 3. This is going to be little
different with LibreNMS. You will need to make changes in here `sudo
vi /etc/httpd/conf.d/librenms.conf`

Your VirtualHost for 443 will need to look something like this for LibreNMS
(obviously changing librenms.example.com to your actual domain name)

```
<VirtualHost *:443>
        SSLEngine on
        SSLCertificateFile /etc/pki/tls/certs/ca.crt
        SSLCertificateKeyFile /etc/pki/tls/private/ca.key
        <Directory "/opt/librenms/html/">
        AllowOverride All
        Require all granted
        Options FollowSymLinks MultiViews
        </Directory>
        DocumentRoot /opt/librenms/html/
        AllowEncodedSlashes NoDecode
        ServerName librenms.example.com
        CustomLog /opt/librenms/logs/access_log combined
        ErrorLog /opt/librenms/logs/error_log
</VirtualHost>

```

### Step 2
Then restart Apache `sudo systemctl restart httpd`

### Step 3
Follow the rest of the How To, especially if you have the server's firewall enabled.
