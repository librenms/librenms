source: General/Callback-Stats-and-Privacy.md
path: blob/master/doc/

# Stats data and your privacy

This document has been put together to explain what LibreNMS does when
it calls back home to report some anonymous statistics.

Let's start off by saying, all of the code that processes the data and
submits it is included in the standard LibreNMS branch you've
installed, the code that accepts this data and in turn generates some
pretty graphs is all open source and available on GitHub. Please feel
free to review the code, comment on it and suggest changes /
improvements. Also, don't forget - by default installations DO NOT
call back home, you need to opt into this.

Above all we respect users privacy which is why this system has been
designed like it has.

Now onto the bit you're interested in, what is submitted and what we
do with that data.

# What is submitted

- All data is anonymous.
- Generic statistics are taken from the database, these include things
  like device count, device type, device OS, port types, port speeds,
  port count and BGP peer count. Take a look at the code for full
  details.
- Pairs of sysDescr and sysObjectID from devices with a small amount
  of sanitation to prevent things like hostnames from being submitted.
- We record version numbers of php, mysql, net-snmp and rrdtool
- A random UUID is generated on your own install.
- That's it!
- Your IP isn't logged, even via our web service accepting the
  data. We don't need to know who you are so we don't ask.

# What we do with the data

- We store it, not for long - 3 months at the moment although this could change.
- We use it to generate pretty graphs for people to see.
- We use it to help prioritise issues and features that need to be worked on.
- We use sysDescr and sysObjectID to create unit tests and improve OS discovery

# Questions?

- **Q.** How often is data submitted? **A.** We submit the data once a
  day according to running daily.sh via cron. If you disable this then
  opting in will not have any affect.
- **Q.** Where can I see the data I submitted? **A.** You can't see
  the data raw, but we collate all of the data together and provide a
  dynamic site so you can see the results of all contributed stats
  [here](https://stats.librenms.org)
- **Q.** I want my data removed. **A.** That's easy, simply press
  'Clear remote stats' in the About LibreNMS page of your control
  panel, the next time the call back script is run it will remove all
  the data we have.
- **Q.** I clicked the 'Clear remote stats' button by accident. **A.**
  No problem, before daily.sh runs again - just opt back in, all of
  your existing data will stay.

Hopefully this answers the questions you might have on why and what we
are doing here, if not, please pop into our [discord
server](https://t.libren.ms/discord) or community forum and ask any
questions you like.

# How do I enable stats submission?

If you're happy with all of this - please consider switching the call
back system on, you can do this within the About LibreNMS page within
your control panel. In the Statistics section you will find a toggle
switch to enable / disable the feature. If you've previously had it
switched on and want to opt out and remove your data, click the 'Clear
remote stats' button and on the next submission all the data you've
sent us will be removed!
