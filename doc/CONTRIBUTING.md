Guidelines for contributing to LibreNMS
---------------------------------------

- Don't submit code whose license conflicts with the GPLv3.  If you're not
  sure, consult the [Free Software Foundation's license list][1] and see if
  your code's license is on the compatible or incompatible list.
  - The SNMP MIBs may be moved to a separate repository soon due to this
    issue.  We will do everything we can to ensure this has minimal impact.
  - The current Observium license is incompatible with GPLv3.  Do not submit
    patches from current Observium unless you are the copyright holder, and
    specifically note that you are releasing it under GPLv3.

- Test your patches first.  It's easy to set up git to push to a bare
  repository on a local test system, and pull from this into your live
  installation at very frequent intervals.

- Don't break the poller.  User interface blemishes are not critical, but
  losing data from network monitoring systems might be.

- Please join us in IRC at irc.freenode.net in channel ##librenms if you're
  able.  Collaborating in real time makes the coordination of contributions
  easier.


Proposed workflow for submitting pull requests (currently untested)
-------------------------------------------------------------------

This is a proposed workflow designed to minimise the scope of merge
conflicts when submitting pull requests:
- Fork the [LibreNMS repo master branch][2] in your own GitHub account.
- Create an [issue][3] explaining what work you plan to do.
- Create a branch in your copy of the repo called issue-###, where ### is
  the issue number you created.
- Make and test your changes in the issue branch as needed - this might take
  a few days or weeks.
- When you are happy with your issue branch's changes and ready to submit
  your patch, update your copy of the master branch to the current revision;
  this should just result in a fast forward of your copy of master.
- Rebase your issue branch from your clone of master.  Fix any conflicts at
  this stage.
- Merge your issue branch back into of your copy of master. Again, this
  should be a simple fast forward.
- Submit a pull request for your patch from your copy of master.

[1]: http://www.gnu.org/licenses/license-list.html
"Free Software Foundation's license list"
[2]: https://github.com/librenms/librenms/tree/master
"LibreNMS master branch"
[3]: https://github.com/librenms/librenms/issues
"LibreNMS issue database"

