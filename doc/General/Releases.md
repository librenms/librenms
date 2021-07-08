source: General/Releases.md
path: blob/master/doc/

# Choosing a release

We try to ensure that breaking changes aren't introduced by utilising
various automated code testing, syntax testing and unit testing along
with manual code review. However bugs can and do get introduced as
well as major refactoring to improve the quality of the code base.

We have two branches available for you to use. The default is the `master` branch.

## Development branch

Our `master` branch is our dev branch, this is actively commited to
and it's not uncommon for multiple commits to be merged in daily. As
such sometimes changes will be introduced which will cause unintended
issues. If this happens we are usually quick to fix or revert those changes.

We appreciate everyone that runs this branch as you are in essence
secondary testers to the automation and manually testing that is done
during the merge stages.

You can configure your install (this is the default) to use this
branch by setting `$config['update_channel'] = 'master';` in
`config.php` and ensuring you switch to the master branch with:

`cd /opt/librenms && git checkout master`

## Stable branch

With this in mind, we provide a monthly stable release which is
released on or around  the last Sunday of the month. Code pull
requests (aside from Bug fixes) are stopped days leading up to the
release to ensure that we have a clean working branch at that point.

The changelog is also updated and will reference the release number
and date so you can see what changes have been made since the last release.

To switch to using stable branches you can set
`$config['update_channel'] = 'release';` in config.php and then switch
to the latest release branch with:

`cd /opt/librenms && git fetch --tags && git checkout $(git describe
--tags $(git rev-list --tags --max-count=1))`
