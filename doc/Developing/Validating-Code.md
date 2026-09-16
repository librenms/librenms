#### Validating Code

The GitHub pull request process runs automated build tests. These tests
make sure that the code has no error, that it obeys the standards, and
that the test suite builds correctly.

You can run these checks yourself before the pull request. The merge is
then faster.

> Run all these commands in the librenms directory. Run them as the
> librenms user, unless the text gives a different user.

Install composer. Skip this step if composer is already installed.

`curl -sS https://getcomposer.org/installer | php`

Composer is then in `/opt/librenms/composer.phar`.

Then install the dependencies:

`./composer.phar install`

After the installation of composer, run the code validation script:

`./lnms dev:check`

The output `Tests ok, submit away :)` means that the code is correct.
Any other output gives the information to correct the problems. Then
run the tests again.

#### Git Hooks

Git has a hook system. A hook starts a check at a given stage. Put
`./lnms dev:check` into your commit process.

Add `./lnms dev:check` to your `.git/hooks/pre-commit`:

    echo "/opt/librenms/lnms dev:check" >> /opt/librenms/.git/hooks/pre-commit
    chmod +x /opt/librenms/.git/hooks/pre-commit
