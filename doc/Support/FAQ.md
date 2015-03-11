### Getting started
 - [How do I install LibreNMS?](#faq1)
 - [How do I add a device?](#faq2)
 - [How do I get help?](#faq3)
 - [What are the supported OSes for installing LibreNMS on?](#faq4)
 - [Do you have a demo available?](#faq5)

### Support
 - [Why do I get blank pages sometimes in the WebUI?](#faq6)
 - [How do I debug pages not loading correctly?](#faq7)

### Developing
 - [How do I add support for a new OS?](#faq8)

#### <a name="faq1"> How do I install LibreNMS?</a>

This is currently well documented within the doc folder of the installation files.

For Debian / Ubuntu installs follow [Debian / Ubuntu](http://docs.librenms.org/Installation/Installation-(Debian-Ubuntu)

For RedHat / CentOS installs follow [RedHat / CentOS](http://docs.librenms.org/Installation/Installation-(RHEL-CentOS))

#### <a name="faq2"> How do I add a device?</a>

You have two options for adding a new device into LibreNMS.

 1. Using the command line via ssh you can add a new device by changing to the directory of your LibreNMS install and typing (be sure to put the correct details).

```ssh
./addhost.php [community] [v1|v2c] [port] [udp|udp6|tcp|tcp6]
```

 2. Using the web interface, go to Devices and then Add Device. Enter the details required for the device that you want to add and then click 'Add Host'.

#### <a name="faq3"> How do I get help?</a>

We have a few methods for you to get in touch to ask for help.

[Mailing List](https://groups.google.com/forum/#!forum/librenms-project)

[IRC](https://webchat.freenode.net/) Freenode ##librenms

[Bug Reports](https://github.com/librenms/librenms/issues)

#### <a name="faq4"> What are the supported OSes for installing LibreNMS on?</a>

Supported is quite a strong word :) The 'officially' supported distros are:

 - Ubuntu / Debian
 - Red Hat / CentOS
 - Gentoo

However we will always aim to help wherever possible so if you are running a distro that isn't one of the above then give it a try anyway and if you need help then jump on the irc channel.

#### <a name="faq5"> Do you have a demo available?</a>

We do indeed, you can find access to the demo [here](demo.librenms.org)

#### <a name="faq6"> Why do I get blank pages sometimes in the WebUI?</a>

The first thing to do is to add /debug=yes/ to the end of the URI (I.e /devices/debug=yes/).

If the page you are trying to load has a substantial amount of data in it then it could be that the php memory limit needs to be increased in php.ini and then your web service reloaded.

#### <a name="faq7"> How do I debug pages not loading correctly?</a>

A debug system is in place which enables you to see the output from php errors, warnings and notices along with the MySQL queries that have been run for that page.

To enable the debug option, add /debug=yes/ to the end of any URI (I.e /devices/debug=yes/) or ?debug=yes if you are debugging a graph directly.

You will then have a two options in the footer of the website - Show SQL Debug and Show PHP Debug. These will both popup that pages debug window for you to view. If the page itself has generated a fatal error then this will be displayed directly on the page.

#### <a name="faq8"> How do I add support for a new OS?</a>

The easiest way to show you how to do that is to link to an existing pull request that has been merged in on [GitHub](https://github.com/librenms/librenms/pull/352/files)

To go into a bit more detail, the following are usually needed:

**includes/definitions.inc.php**
Update this file to include the required definitions for the new OS.
**includes/discovery/os/ciscowlc.inc.php**
This file just sets the $os variable, done by checking the sysDescr snmp value for a particular value that matches the OS you are adding.
**includes/polling/os/ciscowlc.inc.php**
This file will usually set the variables for $version and $hardware gained from an snmp lookup.
**html/images/os/$os.png**
This is a 32x32 png format image of the OS you are adding support for.

