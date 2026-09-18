# Creating a release

### GitHub

You can create a new release on [GitHub](https://github.com/librenms/librenms/releases/new).

Enter the tag version of that month. For September 2016, enter `201609`.

Enter a title. We usually use the form `August 2016 Release`.

Enter a placeholder for the body. You edit this text later.

### Create changelog

This section assumes a release from the master branch.

The changelog comes from the GitHub API. The state of your local branch
therefore does not matter. The branch needs only the code that
generates the changelog.

The GitHub API gives the labels of the merged pull requests. These
labels put each entry into a category. The API also gives the author of
each pull request. The changelog then thanks each author.

The command asks for a GitHub personal access token. Generate this
token on the [GitHub tokens page](https://github.com/settings/tokens).
The token needs no permission. Give it a name and click
`Generate Token`. Then export the token in the environment variable
`GH_TOKEN`, or put it in your `.env` file.

The basic command uses `artisan`. Give the new tag, such as 1.41, and
the previous tag, such as 1.40. For more help, run
`php artisan release:tag --help`. The command generates a changelog up
to the head of the master branch. For a different end point, give the
latest pull request number with `--pr $PR_NUMBER`.

```bash
php artisan release:tag 1.41 1.40
```

- Commit and push the change to `doc/General/Changelog.md`.
- After the merge of the changelog pull request, create a new release
  on [GitHub](https://github.com/librenms/librenms/releases/new).
- Create two threads on the community site:
  - A changelog thread [example](https://community.librenms.org/t/v1-40-release-changelog-may-2018/4228/1)
  - An info thread [example](https://community.librenms.org/t/v1-40-may-2018-info/4229/)
- [Tweet it](https://twitter.com/librenms)
- [Facebook it](https://www.facebook.com/LibreNMS/)
- [Google Plus it](https://plus.google.com/u/1/b/110467424837711353117/)
- [LinkedIn it](https://www.linkedin.com/company/librenms/)
