Contributor Agreement
---------------------

By contributing code to LibreNMS (whether by a github pull request, or by
any other means), you assert that:

- You have the rights to include the code, either as its original author,
  or due to it being released to you under a compatible license.

- You are not aware of any third party claims on the code, including
  copyright infringement, patent, or any other claim.

- You have not viewed code written under the [Observium License][4] in the
  production of contributed code.  This includes all Observium code after
  Subversion revision 3250 and any patches or other code covered by that
  license from Observium web sites after Tue May 29 13:08:01 2012 +0000 (the
  date of Observium r3250).

To agree with these assertions, please submit a github pull request against
[AUTHORS.md][5] including your name, email address, and github user id in
the file (so that it can be matched to your commits), and stating in the
commit log:
```
	I agree to the conditions of the Contributor Agreement
	contained in doc/General/Contributing.md.
```


Copyright
---------

All contributors to LibreNMS retain copyright to their own code and are not
required to sign over their rights to any other party.

We recommend that if you add a new file containing original code to the code
base that you include a copyright notice in it as per the Free Software
Foundation's guidelines.  You might find something like the following header
appropriate (although this is not legal advice ;-):
```
  <?php
  /*
   * LibreNMS module to frob blurgs from a foo bar
   *
   * Copyright (c) 2014 Internet Widgitz Pty Ltd <http://example.com/>
   *
   * This program is free software: you can redistribute it and/or modify it
   * under the terms of the GNU General Public License as published by the
   * Free Software Foundation, either version 3 of the License, or (at your
   * option) any later version.  Please see LICENSE.txt at the top level of
   * the source code distribution for details.
   */
  ?>
```
The GPLv3 itself also contains recommendations about applying the GPL to
your code.  Please see LICENSE.txt at the top of this source code
distribution for details.


General Guidelines
------------------

- Test your patches first.  It's easy to set up git to push to a bare
  repository on a local test system, and pull from this into a live test
  installation at very frequent intervals.

- Don't break the poller.  User interface blemishes are not critical, but
  losing data from network monitoring systems might be.

- As a general rule, if you're replacing lines of code with new lines of
  code, don't comment them out, just delete them.  Commented out code makes
  the patch and the resultant code harder to read, and there's no good
  reason to it since we can easily get them back from git.

- If you're fixing a bug or making another minor change, don't reformat the
  code at the same time.  This makes it harder to see what's changed.  If
  you need to reformat it after making the change, do so in a separate
  commit.

- Please join us in IRC at irc.freenode.net in channel ##librenms if you're
  able.  Collaborating in real time makes the coordination of contributions
  easier.

- Ensure you read the Code Guidelines documention and understand the code style that should be adhered to [6]. 


Integrating other code
----------------------

Giving credit where credit is due is critical to the Free Software
philosophy.  If you use code from somewhere else, even if it's trivial,
be sure to note this as a comment in the code (preferably) or the commit
message.

- To incorporate larger blocks of code from third parties (e.g. JavaScript
  libraries):
    - Include its name, source URL, copyright notice, and license in
      doc/General/Credits.md
    - preferred locations are html/js, html/lib, and lib
    - Add it in a separate commit into its own directory, using
      'git subtree --squash' if it is available via git.
    - Add the code to integrate it in a separate commit.  Include:
        - code to update it in Makefile
	- Scrutinizer exclusions to .scrutinizer.yml
	- symlinks where necessary to maintain sensible paths

- Don't submit code whose license conflicts with the GPLv3.  If you're not
  sure, consult the [Free Software Foundation's license list][1] and see if
  your code's license is on the compatible or incompatible list.  If you
  prefer a non-copyleft license, Apache 2.0 is the recommended choice as per
  the FSF guidelines.

- The current Observium license is incompatible with GPLv3.  Don't submit
  code from current Observium unless you are the copyright holder, and you
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
  does not apply to them.  This is not a legal ruling - it is simply a
  statement of our intent and current interpretation.


Proposed workflow for submitting pull requests
----------------------------------------------

The basic rule is: don't create merge conflicts in master.  Merges should be
simple fast-forwards from current master.

Following is a proposed workflow designed to minimise the scope of merge
conflicts when submitting pull requests.  It's not mandatory, but seems to
work well enough.

We don't recommend git flow because we don't want to maintain separate
development and master branches, but if it works better for you, feel free
to do that, as long as you follow the golden rule of not creating merge
conflicts in master.

Workflow:

- Ensure you have auto rebase switched on in your gitconfig.
```
[branch]
        autosetuprebase = always
```
- Fork the [LibreNMS repo master branch][2] in your own GitHub account.
- Create an [issue][3] explaining what work you plan to do.
- Create a branch in your copy of the repo called issue-####, where #### is
  the issue number you created.
```
git push origin master:issue-####
```
- Make and test your changes in the issue branch as needed - this might take
  a few days or weeks.
- When you are happy with your issue branch's changes and ready to submit
  your patch, update your copy of the master branch to the current revision;
  this should just result in a fast forward of your copy of master:
```
git checkout master
git pull
```
- Rebase your issue branch from your clone of master; fix any conflicts at
  this stage:
````
git checkout issue-####
git pull
````
- Push your changes to your remote git hub branch so you can submit a pull from your issue-#### branch:
````
git push origin issue-####
````
- Submit a pull request for your patch from your issue-#### branch.

[1]: http://www.gnu.org/licenses/license-list.html
"Free Software Foundation's license list"
[2]: https://github.com/librenms/librenms/tree/master
"LibreNMS master branch"
[3]: https://github.com/librenms/librenms/issues
"LibreNMS issue database"
[4]: http://www.observium.org/wiki/License
"Observium License"
[5]: https://github.com/librenms/librenms/blob/master/AUTHORS.md
"LibreNMS contributor list"
[6]: https://github.com/librenms/librenms/blob/master/doc/Developing/Code-Guidelines.md
