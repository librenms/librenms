Welcome to Observium users
--------------------------

LibreNMS is a fork of Observium.  The reason for the fork is nothing to do
with Observium's [move to community vs. paid versions][1].  It is simply
that we have different priorities and values to the Observium development
team.  We decided to fork (reluctantly) because we like using Observium,
but we want to collaborate on a community-based project with like-minded
IT professionals.  See [README.md][2] and the references there for more
information about the kind of community we're trying to promote.

LibreNMS was forked from [the last GPL-licensed version of Observium][3].
This means you won't be able to take an existing Observium installation
later than r3250 and just change it to LibreNMS.  This would probably break
(although if you were on a version between r3250 and the next database
schema change, it might be feasible).  Upgrades from versions earlier than
r3251 might work.  Please try it on an unimportant system and tell us your
experiences!

How LibreNMS will be different from Observium:
- We will have an inclusive community, where it's OK to ask stupid
  questions, and OK to ask for things that aren't on the roadmap.  If you'd
  like to see something added, add or comment on the relevant issue in our
  [GitHub issue database][9].
- Development decisions will be community-driven.  We want to make software
  that fulfills its users' needs.  See the [ROADMAP][4] for more thoughts
  on our current plans.
- ~~Development will probably proceed at a slower pace, at least initially.~~
- There are no plans for a paid version, and we don't anticipate this ever
  changing.
- There are no current plans for paid support, but this may be added later
  if there is sufficient demand.
- We use git for version control and GitHub for hosting to make it as easy
  and painless as possible to create forked or private versions.

Reasons why you might want to use Observium instead of LibreNMS:
- You have a financial investment in Observium and aren't concerned about
  community contributions.
- ~~You need functionality that has been added to Observium since r3250.~~ The beauty of LibreNMS is that you can contribute missing features.
- You don't like the [GNU General Public License, version 3][5] or the
  [philosophy of Free Software/copyleft][6] in general.

Reasons why you might want to use LibreNMS instead of Observium:
- You want to work with others on the project, knowing that [your
  investment of time and effort will not be wasted][7].
- You want to add and experiment with features that are not a priority for
  the Observium developers.  See [CONTRIBUTING][8] for more details.
- You want to make use of the additional features LibreNMS can offer.

[1]: http://postman.memetic.org/pipermail/observium/2013-October/003915.html
"Observium edition split announcement"
[2]: https://github.com/librenms/librenms/blob/master/README.md
"LibreNMS README"
[3]: http://fisheye.observium.org/rdiff/Observium?csid=3251&u&N
"Link to Observium license change"
[4]: https://github.com/librenms/librenms/blob/master/doc/General/Roadmap.md
"LibreNMS ROADMAP"
[5]: https://github.com/librenms/librenms/blob/master/LICENSE.txt
"LibreNMS copy of GPL v3"
[6]: http://www.gnu.org/philosophy/free-sw.html
"Free Software Foundation - what is free software?"
[7]: http://libertysys.com.au/blog/observium-and-gpl
"Paul's blog on what the GPL offers users"
[8]: https://github.com/librenms/librenms/blob/master/doc/General/Contributing.md
"Contribution guidelines"
[9]: https://github.com/librenms/librenms/issues
"LibreNMS issue database at GitHub"


