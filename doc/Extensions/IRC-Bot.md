source: Extensions/IRC-Bot.md
path: blob/master/doc/
[TOC]

# About

LibreNMS has an easy to use IRC-Interface for basic tasks like viewing
last log-entry, current device/port status and such.

By default the IRC-Bot will not start when executed and will return an
error until at least `$config['irc_host']` and `$config['irc_port']`
has been specified inside `config.php`. (To start the IRC-Bot run
./irc.php )

If no channel has been specified with `$config['irc_chan']`,
`##librenms` will be used. The default Nick for the bot is `LibreNMS`.

The Bot will reply the same way it's being called. If you send it the
commands via Query, it will respond in the Query. If you send the
commands via a Channel, then it will respond in the Channel.

## Configuration & Defaults

Option | Default-Value | Notes
--- | --- | ---
`$config['irc_alert']` | `false` | Optional; Enables Alerting-Socket. `EXPERIMENTAL`
`$config['irc_alert_chan']` | `false` | Optional; Multiple channels can be defined as Array or delimited with `,`. `EXPERIMENTAL`
`$config['irc_alert_utf8']` | `false` | Optional; Enables use of strikethrough in alerts via UTF-8 encoded characters. Might cause trouble for some clients.
`$config['irc_alert_short']` | `false` | Optional; Send a one line alert summary instead of multi-line detailed alert.
`$config['irc_authtime']` | `3` | Optional; Defines how long in Hours an auth-session is valid.
`$config['irc_chan']` | `##librenms` | Optional; Multiple channels can be defined as Array or delimited with `,`. Passwords are defined after a `space-character`.
`$config['irc_debug']` | `false` | Optional; Enables debug output (Wall of text)
`$config['irc_external']` |  | Optional; Array or `,` delimited string with commands to include from `includes/ircbot/*.inc.php`
`$config['irc_host']` |  | Required; Domain or IP to connect. If it's an IPv6 Address, embed it in `[]`.  (Example: `[::1]`)
`$config['irc_maxretry']` | `5` | Optional; How many connection attempts should be made before giving up
`$config['irc_nick']` | `LibreNMS` | Optional;
`$config['irc_pass']` |  | Optional; This sends the IRC-PASS Sequence to IRC-Servers that require Password on Connect
`$config['irc_port']` | `6667` | Required; To enable SSL append a `+` before the Port. (Example: `+6697`)
`$config['irc_ctcp']` | `false` | Optional; Enable/disable ctcp-replies from the bot (currently VERSION, PING and TIME).
`$config['irc_ctcp_version']` | `LibreNMS IRCbot. https://www.librenms.org/` | Optional: Reply-string to CTCP VERSION requests
`$config['irc_auth']` |  | Optional: Array of hostmasks that are automatically authenticated.

### IRC-Commands

Command | Description
--- | ---
`.auth <User/Token>` | If `<user>`: Request an Auth-Token. If `<token>`: Authenticate session.
`.device <hostname>` | Prints basic information about given `hostname`.
`.down` | List hostnames that are down, if any.
`.help` | List available commands.
`.join <channel>` | Joins `<channel>` if user has admin-level.
`.listdevices` | Lists the hostnames of all known devices.
`.log [<N>]` | Prints `N` lines or last line of the eventlog.
`.port <hostname> <ifname>` | Prints Port-related information from `ifname` on given `hostname`.
`.quit` | Disconnect from IRC and exit.
`.reload` | Reload configuration.
`.status <type>` | Prints status information for given `type`. Type can be `devices`, `services`, `ports`. Shorthands are: `dev`,`srv`,`prt`
`.version` | Prints `$this->config['project_name_version']`.

( __/!\__ All commands are case-_insensitive_ but their arguments are case-_sensitive_)

# Examples

## Server examples:

Unencrypted Connection to `irc.freenode.org`:

```php
   ...
   $config['irc_host'] = "irc.freenode.org";
   $config['irc_port'] = 6667;
   ...
```

SSL-Encrypted Connection to `irc.freenode.org`:

```php
   ...
   $config['irc_host'] = "irc.freenode.org";
   $config['irc_port'] = "+6697";
   ...
```

SSL-Encrypted Connection to `irc.localdomain` with Server-Password and odd port:

```php
   ...
   $config['irc_host'] = "irc.localdomain";
   $config['irc_port'] = "+12345";
   $config['irc_pass'] = "Super Secret Passphrase123";
   ...
```

## Channel notations:

Channels can be defined using Array-Notation like:

```php
   ...
   $config['irc_chan'][] = "#librenms";
   $config['irc_chan'][] = "#otherchan";
   $config['irc_chan'][] = "#noc";
   ...
```

Or using a single string using `,` as delimiter between various channels:

```php
   ...
   $config['irc_chan'] = "#librenms,#otherchan,#noc";
   ...
```

## Hostmask authentication:

```php
   ...
   $config['irc_auth']['admin'][] = "*!root@nms.host.invalid";
   $config['irc_auth']['admin'][] = "*!*peter@peters.computer.invalid";
   $config['irc_auth']['john][] = "john!doe@login.server.invalid";
   ...
```

Any client matching one of the first two hostmasks will automatically
be authenticated as the "admin" user in LibreNMS, and clients matching
the last line will be authenticated as the user "john" in LibreNMS,
without using .auth and a waiting for a valid token.

# Extensions?!

The bot is coded in a unified way.
This makes writing extensions by far less painful.
Simply add your `command` to the `$config['irc_external']` directive
and create a file called `includes/ircbot/command.inc.php` containing
your code. The string behind the call of `.command` is passed as `$params`.
The user who requested something is accessible via `$this->user`.
Send your reply/ies via `$this->respond($string)`.

A more detailed documentation of the functions and variables available
for extensions can be found at [IRC-Bot
Extensions](IRC-Bot-Extensions);

Confused? Here an Echo-Example:

File: config.php

```php
   ...
   $config['irc_external'][] = "echo";
   ...
```

File: includes/ircbot/echo.inc.php

```php
   //Prefix everything with `You said: '...'`  and return what was sent.
   if( $this->user['name'] != "root" ) {
      return $this->respond("You said: '".$params."'");
   } else {
      return $this->respond("root shouldn't be online so late!");
   }
```

# Systemd start up script

Basic systemd start up script to be placed in /etc/systemd/system/ to
start irc service at boot.

librenms-irc.service script is located at /opt/librenms/misc/

Once copied to /etc/systemd/system/ you must run the following commands:

a) chmod 664 /etc/systemd/system/librenms-irc.service

b) systemctl daemon-reload

c) systemctl enable librenms-irc.service

d) systemctl start librenms-irc.service

It can be stopped or started just like any other systemd script such
as systemctl start librenms-irc.service

