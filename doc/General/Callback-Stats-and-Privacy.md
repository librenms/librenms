# Submitting Stats

## Stats data and your privacy

This document explains what LibreNMS sends when it reports anonymous
statistics to us.

The code that collects and sends the data is part of the standard
LibreNMS branch that you installed. The code that accepts the data and
draws the graphs is also open source and available on GitHub. You can
read this code, comment on it, and suggest changes.

By default, an install does not send any data. You must opt in.

We respect the privacy of our users. This principle controls the design
of the whole system.

## What is submitted

- All data is anonymous.
- Generic statistics come from the database. These statistics include
  the device count, the device type, the device OS, the port types, the
  port speeds, the port count, and the BGP peer count. The code gives
  the full list.
- Pairs of sysDescr and sysObjectID come from your devices. LibreNMS
  sanitizes these pairs to keep out data such as hostnames.
- LibreNMS records the version numbers of php, mysql, net-snmp, and
  rrdtool.
- LibreNMS generates a random UUID on your own install.
- LibreNMS sends nothing else.
- We do not log your IP address, not even in the web service that
  accepts the data. We do not need to know who you are.

## What we do with the data

- We store the data for 3 months. This period can change.
- We use the data to draw graphs for people to see.
- We use the data to set the priority of the problems and the features
  that need work.
- We use sysDescr and sysObjectID to write unit tests and to improve OS
  discovery.

## How do I enable stats submission?

To enable the callback system, open the About LibreNMS page in your
control panel. In the Statistics section, use the toggle switch.

To opt out and remove your data, click the `Clear remote stats` button.
LibreNMS removes all the data that you sent on the next submission.

## Questions?

### How often is data submitted?

LibreNMS sends the data one time each day as part of `daily.sh`.
If you disable `daily.sh`, an opt-in has no effect.

### Where can I see the data I submitted?

You cannot see your raw data. We combine all the data and show the
result on a dynamic site. See the [statistics
site](https://stats.librenms.org).

### I want my data removed.

Click `Clear remote stats` on the About LibreNMS page of your control
panel. The callback script removes all the data that we hold on the
next run.

### I clicked the 'Clear remote stats' button by accident.

Opt back in before the next run of `daily.sh`. All your existing data
stays.

If you have more questions, ask on our [discord
server](https://t.libren.ms/discord) or on the community forum.
