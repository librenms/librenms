# General

Like any good software we take security seriously. However, bugs do
make it into the software along with the history of the code base we
inherited. It's how we deal with identified vulnerabilities that
should show that we take things seriously.

## Securing your install

As with any system of this nature, we highly recommend that you
restrict access to the install via a firewall or VPN.

Please ensure you keep your install [up to date](Updating.md).

### Enable HTTPS

It is also highly recommended that the Web interface is protected with
an SSL certificate such as ones provided by [LetsEncrypt](http://www.letsencrypt.org).

### Secure Session Cookies

Once you have enabled HTTPS for your install, you should set `SESSION_SECURE_COOKIE=true`
in your .env file.  This will require cookies to be transferred by secure protocol and
prevent any MiM attacks against it.

### Trusted Proxies

When using a reverse proxy, you may restrict the hosts allowed to forward
headers to LibreNMS. By default this allows all proxies, due to legacy reasons.

Set APP_TRUSTED_PROXIES in your .env to an empty string or the urls to
the proxies allowed to forward.

## Reporting vulnerabilities

Like anyone, we appreciate the work people put in to find flaws in
software and welcome anyone to do so with LibreNMS, this will lead to
better quality and more secure software for everyone.

If you think you've found a vulnerability and want to discuss it with
some of the core team then you can contact us on
[Discord](https://discord.com/invite/librenms) and we will endeavour to
get back to as quick as we can, this is usually within 24 hours.

We are happy to attribute credit to the findings, but we ask that we're
given a chance to patch any vulnerability before public disclosure so
that our users can update as soon as a fix is available.

