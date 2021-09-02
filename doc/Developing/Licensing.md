## Contributor Agreement

path: blob/master/doc/

By contributing code to LibreNMS (whether by a GitHub pull request, or by
any other means), you assert that:

- You have the rights to include the code, either as its original author,
  or due to it being released to you under a compatible license.

- You are not aware of any third party claims on the code, including
  copyright, patent, trademark, or any other legal claim.

- You have acknowledged in the content of your contribution (usually as a
  source code comment) any and all sources and influences used in the
  production of that contribution.

- You have not viewed code written under the [Observium
  License](http://www.observium.org/wiki/License) in the
  production of contributed code.  This includes all Observium code after
  Subversion revision 3250 and any patches or other code covered by that
  license after Tue May 29 13:08:01 2012 +0000 (the date of Observium r3250).

To agree with these assertions, when you submit your first pull
request you  will be asked after submitting to sign the CLA, you do
this by following the  link provided in the PR and agreeing to the CLA
using your GitHub account.

## Local patches

Please note that the above contributor agreement means that if you have
developed a feature for a non-GPL version of Observium, we can't include it
in LibreNMS, even if you have not released it to the public.  If there's a
feature you use to which this applies, please document its functionality in
an issue, and we'll do our best to include equivalent functionality in
LibreNMS.

## Copyright

We recommend that if you add a new file containing original code to the code
base that you include a copyright notice in it as per the Free Software
Foundation's guidelines.  You might find something like the following header
appropriate (although this is not legal advice ;-). Please also ensure
you add the package information to the header.

```
<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2017 Internet Widgitz Pty Ltd <info@widgitz.com>
 * @author     Me <me@infowidgitz.com>

```

The GPLv3 itself also contains recommendations about applying the GPL to
your code.  Please see LICENSE.txt at the top of this source code
distribution for details.

## Integrating other code

Giving credit where credit is due is critical to the Free Software
philosophy.  If you use code from somewhere else, even if it's trivial,
be sure to note this as a comment in the code (preferably) or the commit
message.  Accurate attribution is crucial to our success as a Free Software
project.

- For any dependency
  - Include its name, source URL, copyright notice, and license in `doc/General/Credits.md`

- To add a php dependency, please use composer
  - Add the dependency `composer require slim/slim`

  - Updating php dependencies
    - Update dependencies `FORCE=1 php56 ./scripts/composer_wrapper.php update`
    - Commit the updated composer.lock file

- To add a javascript dependency
  - Where possible please include minimized libraries in the html/js/ folder.

- Don't submit code whose license conflicts with the GPLv3.  If you're not
  sure, consult the [Free Software Foundation's license
  list](https://www.gnu.org/licenses/license-list.html) and see if
  your code's license is on the compatible or incompatible list.  If
  you   prefer a non-copyleft license, Apache 2.0 is the recommended choice as per
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
  of misappropriating Observium's code.  As the code bases develop, we
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
