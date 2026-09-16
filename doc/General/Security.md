# General

We take security seriously. Bugs still get into the software. The code
base that we inherited also contains bugs. Our response to a known
vulnerability shows how seriously we take security.

## Securing your install

Restrict access to your install with a firewall or a VPN.

Keep your install [up to date](Updating.md).

### Enable HTTPS

Protect the web interface with an SSL certificate.
[LetsEncrypt](http://www.letsencrypt.org) supplies free certificates.

### Secure Session Cookies

After you enable HTTPS, set `SESSION_SECURE_COOKIE=true` in your `.env`
file. This setting sends cookies only over a secure protocol. It also
prevents man-in-the-middle attacks against the cookies.

### Trusted Proxies

If you use a reverse proxy, you can restrict the hosts that forward
headers to LibreNMS. By default, LibreNMS accepts all proxies. This
default exists for legacy reasons.

In your `.env` file, set `APP_TRUSTED_PROXIES` to an empty string. You
can also set it to the URLs of the proxies that can forward headers.

## Reporting vulnerabilities

We value the work that people do to find flaws in software. Anyone can
look for flaws in LibreNMS. This work makes the software better and
more secure for everyone.

If you find a vulnerability, contact the core team on
[Discord](https://discord.com/invite/librenms). We answer as quickly as
we can, usually within 24 hours.

We give credit for each finding. We ask for the time to patch a
vulnerability before public disclosure. Our users can then update as
soon as a fix is available.

