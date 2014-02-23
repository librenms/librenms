Guidelines for contributing to LibreNMS
---------------------------------------

- Test your patches first.  It's easy to set up git to push to a bare
  repository on a local test system, and pull from this into your live
  installation at very frequent intervals.

- Don't break the poller.  User interface blemishes are not critical, but
  losing data from network monitoring systems might be.

- Please join us in IRC at irc.freenode.net in channel ##librenms if you're
  able.  Collaborating in real time makes the coordination of contributions
  easier.

- Don't submit code whose license conflicts with the GPLv3.  If you're not
  sure, consult the [Free Software Foundation's license list][1] and see if
  your code's license is on the compatible or incompatible list.  If you
  prefer a non-copyleft license, Apache 2.0 is the recommended choice as per
  the FSF guidelines.

- The current Observium license is incompatible with GPLv3.  Don't submit
  code from current Observium unless you are the copyright holder, and
  specifically state in the code that you are releasing it under GPLv3 (or a
  compatible license).

  Because contributing to Observium requires that you reassign copyright to
  Adam Armstrong, if you want to release the same code for both Observium
  and LibreNMS, you need to release it for LibreNMS first and mark it with
  your own copyright notice, then release it to Observium and remove your
  copyright, granting Adam ownership.

  Please note that the above is necessary even if you don't care about
  keeping the copyright to your code, because otherwise we could be accused
  of misappropriating Obserivum's code.  As the code bases develop, we
  expect them to diverge, which means this will become less of an issue
  anyway.

- Because the GPL's provisions about linking don't apply to PHP-based
  projects, we interpret the linking provisions of the license to refer to
  the use of PHP library functions called from LibreNMS code.

  We consider inclusion of files such as MIBs in the LibreNMS repository to
  be merely aggregation in a distribution medium as per the last paragraph
  of the GPLv3 section 5 ("Conveying Modified Source Versions"), and because
  they are not combined with LibreNMS to form a larger program, the GPLv3
  does not apply to them.  This is not a legally binding ruling - it is
  simply a statement of our intent and current interpretation.


Proposed workflow for submitting pull requests
----------------------------------------------

The basic rule is: don't create merge conflicts in master.  If possible,
make your merges simple fast-forwards from current master.

Following is a proposed workflow designed to minimise the scope of merge
conflicts when submitting pull requests.  It's not mandatory, but seems to
work well enough.

We don't recommend git flow because we don't want to maintain separate
development and master branches, but if it works better for you, feel free
to do that, as long as you follow the golden rule of not.

- Fork the [LibreNMS repo master branch][2] in your own GitHub account.
- Create an [issue][3] explaining what work you plan to do.
- Create a branch in your copy of the repo called issue-####, where #### is
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

