# Choosing a release

We use automated code tests, syntax tests, and unit tests to keep
breaking changes out of the software. We also do manual code review.
Bugs still get introduced. We also do major refactoring to improve the
quality of the code base.

Two branches are available. The default branch is `master`.

## Development branch

The `master` branch is the development branch. We commit to it often,
and we merge several commits on most days. A change can therefore cause
an unintended problem. When this happens, we usually fix or revert the
change quickly.

We value everyone who runs this branch. These users are the second line
of tests after the automated tests and the manual tests at the merge
stage.

To use this branch, set the update channel. This branch is the default:

!!! setting "system/updates"
    ```bash
    lnms config:set update_channel master
    ```

Then move to the master branch:

```bast
cd /opt/librenms
git checkout master
./daily.sh
```

## Stable branch

We also supply a stable release each month. The release comes out near
the middle of the month, usually on a weekday. Before the release, we
stop the merge of pull requests other than bug fixes. The working
branch is then clean at the release.

The [changelog](Changelog.md) gives the release number and the release
date. It shows every change since the last release.

To use the stable branch, set the update channel:

!!! setting "system/updates"
    ```bash
    lnms config:set update_channel release
    ```

This setting pauses updates until the next stable release. LibreNMS then updates
to that release. After that, LibreNMS updates only to stable releases.

!!! warning
    Do not downgrade LibreNMS. A downgrade is not supported and usually causes bugs.
