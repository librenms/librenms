source: General/Security.md
# Security

### General
Like any good software we take security seriously. However, bugs do make it into the software 
along with the history of the code base we inherited. It's how we deal with identified vulnerabilities 
that should show that we take things seriously. 

### Securing your install
As with any system of this nature, we highly recommend that you restrict access to the install via 
a firewall or VPN.

It is also highly recommended that the Web interface is protected with an SSL certificate such as one 
provided by [LetsEncrypt](http://www.letsencrypt.org).

When using HTTPS, it is recommended that you use secure, encrypted cookies to prevent session
hijacking attacks. Set ``$config['secure_cookies'] = true`` in ``config.php`` to enable these.

Please ensure you keep your install [up to date](Updating.md).

### Enabling HTTPS
This example is specifically for Ubuntu 16.04 eith Nginx and uses an SSL certificate from Let's Encrypt.
Please follow [this](https://www.digitalocean.com/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-16-04) excellent tutorial on setting up nginx with an SSL cert from LetsEncrypt.

#### Step 1
Follow the LetsEncrypt tutorial until you get to *Step 2: Obtain and SSL Certificate.* 
In this section you need to change some config in the Nginx configuration. The tutorial directs you to edit `/etc/nginx/sites-available/default` however in the standard LibreNMS install, the file you need to edit is actually `/etc/nginx/conf.d/librenms.conf`.

#### Step 2
When you get to *Step 3: Configure TLS/SSL on Web Server (Nginx)* there are some differences again. 
Follow the instructions regarding the "snippet" configuration, the main differences come when you get to *Adjust the Nginx Configuration to Use SSL*.
Here you are again directed to edit `/etc/nginx/sites-available/default`, so we need to go to `/etc/nginx/conf.d/librenms.conf`.

`sudo nano /etc/nginx/sites-available/default`

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
This config will redirect HTTP traffic to HTTPS and use the SSL config that you have just set up.

Check for syntax errors
`sudo nginx -t`

Then restart Nginx
`sudo systemctl restart nginx`

#### Step 3
Follow the rest of the tutorial, especially if you have the server's firewall enabled.

#### Note regarding the cron job to renew your cert
The tutorial recommends that you put an entry in the root crontab to update the cert automatically.

It would be best to do this in the main librenms cron file instead.

We want to run the command as the librenms user (this avoids ownership issues on log files), but we don't want to have to enter the sudo password when running the cron job.
To get around this edit the following file:

`sudo visudo`

and put this at the end:

`librenms ALL = NOPASSWD: /usr/bin/certbot`

This will allow librenms to run this one command as sudo without entering the sudo password.

Next edit the librenms cron file:

`sudo nano /etc/cron.d/librenms`

Add this to the end:

`15 3 * * * librenms sudo /usr/bin/certbot renew --quiet --renew-hook "/bin/systemctl reload nginx"`

This will run the script at 3.15am every day (you can change this to suit)

### Reporting vulnerabilities
Like anyone, we appreciate the work people put in to find flaws in software and welcome anyone 
to do so with LibreNMS, this will lead to better quality and more secure software for everyone.

If you think you've found a vulnerability and want to discuss it with some of the core team then 
you can email us at [team@librenms.org](team@librenms.org) and we will endeavour to get back to 
as quick as we can, this is usually within 24 hours.

We are happy to attribute credit to the findings but we ask that we're given a chance to patch 
any vulnerability before public disclosure so that our users can update as soon as a fix is 
available.
