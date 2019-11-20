source: Developing/Creating-Release.md
path: blob/master/doc/

# Creating a release

### GitHub

You can create a new release on [GitHub](https://github.com/librenms/librenms/releases/new).

Enter the tag version that month, i.e for September 2016 you would enter `201609`.

Enter a title, we usually use `August 2016 Release`

Enter a placeholder for the body, we will edit this later.

### Create changelog

For this, we assume you are using the master branch to create the release against.

We now generate the changelog using the GitHub API itself so it
shouldn't matter what state your local branch is in so long as it has
the code to generate the changelog itself.

Using the GitHub API means we can use the labels associated with
merged pull requests to categorise the changelog. We also then record
who made the pull request to thank them in the changelog itself.

You will be asked for a GitHub personal access token. You can generate
this [here](https://github.com/settings/tokens). No permissions should
be needed so just give it a name and click `Generate Token`. You can
then export the token as an  environment variable `GH_TOKEN` or place
it in your `.env` file.

The basic command to run is by using `artisan`. Here you pass `new
tag` (1.41) and `previous tag` (1.40). For further  help run `php
artisan release:tag --help`. This will generate a changelog up to the
latest master branch, if you want  it to be done against something
else then pass the latest pull request number with `--pr $PR_NUMBER`.

```bash
php artisan release:tag 1.41 1.40
```

- Now commit and push the change that has been made to `doc/General/Changelog.md`.
- Once the pull request has been merged in for the Changelog, you can
  create a new release on
  [GitHub](https://github.com/librenms/librenms/releases/new).
- Create two threads on the community site:
  - A changelog thread [example](https://community.librenms.org/t/v1-40-release-changelog-may-2018/4228/1)
  - An info thread [example](https://community.librenms.org/t/v1-40-may-2018-info/4229/)
- [Tweet it](https://twitter.com/librenms)
- [Facebook it](https://www.facebook.com/LibreNMS/)
- [Google Plus it](https://plus.google.com/u/1/b/110467424837711353117/)
- [LinkedIn it](https://www.linkedin.com/company/librenms/)
