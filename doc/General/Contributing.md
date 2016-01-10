All contributors to LibreNMS retain copyright to their own code and are not
required to sign over their rights to any other party.


Contributor Agreement
---------------------

By contributing code to LibreNMS (whether by a github pull request, or by
any other means), you assert that:

- You have the rights to include the code, either as its original author,
  or due to it being released to you under a compatible license.

- You are not aware of any third party claims on the code, including
  copyright, patent, trademark, or any other legal claim.

- You have acknowledged in the content of your contribution (usually as a
  source code comment) any and all sources and influences used in the
  production of that contribution.

- You have not viewed code written under the [Observium License][4] in the
  production of contributed code.  This includes all Observium code after
  Subversion revision 3250 and any patches or other code covered by that
  license after Tue May 29 13:08:01 2012 +0000 (the date of Observium r3250).

- You are not running a copy of non-GPLed Observium, whether as part of your
  work duties, or personally, or in any other capacity, and you have removed
  any copies of non-GPLed Observium source code from your personal and work
  systems.


To agree with these assertions, please submit a Github pull request against
[AUTHORS.md][5], adding or altering a **single line** *containing your name,
email address, and Github user id* in the file (so that it can be matched to
your commits), and stating in the *commit log* (not the pull request text):
```
	I agree to the conditions of the Contributor Agreement
	contained in doc/General/Contributing.md.
```

Local patches
-------------

Please note that the above contributor agreement means that if you have
developed a feature for a non-GPL version of Observium, we can't include it
in LibreNMS, even if you have not released it to the public.  If there's a
feature you use to which this applies, please document its functionality in
an issue, and we'll do our best to include equivalent functionality in
LibreNMS.


Copyright
---------

We recommend that if you add a new file containing original code to the code
base that you include a copyright notice in it as per the Free Software
Foundation's guidelines.  You might find something like the following header
appropriate (although this is not legal advice ;-):
```
  <?php
  /*
   * LibreNMS module to frob blurgs from a foo bar
   *
   * Copyright (c) 2015 Internet Widgitz Pty Ltd <http://example.com/>
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
  easier.  If you're not a regular IRC user, just browse to [Freenode
  web chat](http://webchat.freenode.net/) and follow the prompts to chat
  via the web client.

- Ensure you read the Code Guidelines documentation and understand the code
  style that should be adhered to [6].


Integrating other code
----------------------

Giving credit where credit is due is critical to the Free Software
philosophy.  If you use code from somewhere else, even if it's trivial,
be sure to note this as a comment in the code (preferably) or the commit
message.  Accurate attribution is crucial to our success as a Free Software
project.

- To incorporate larger blocks of code from third parties (e.g. JavaScript
  libraries):
    - Include its name, source URL, copyright notice, and license in
      doc/General/Credits.md
    - Where possible please include libraries in the lib/ folder.
    - Add it in a separate commit into its own directory, using
      git subtree if it is available via git:
      git subtree add --squash --prefix=lib/<library name> <library git url> <library branch name>
      I.e:
      ```ssh
      git subtree add --squash --prefix=lib/jquery-bootgrid https://github.com/rstaib/jquery-bootgrid.git master
      ```
    - Add the code to integrate it in a separate commit.  Include:
        - code to update it in Makefile
	- Scrutinizer exclusions to .scrutinizer.yml (not needed if added to lib/ folder).
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

Please see the new [Using Git](http://docs.librenms.org/Developing/Using-Git/) document which gives you step-by-step 
instructions on using git to submit a pull request.

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
