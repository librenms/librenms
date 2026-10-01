Git is difficult at the start. Learn the [basics][1][2] at least.

This short guide helps a new Git user to start work on LibreNMS.

This guide assumes these conditions:

- The work is on a Linux machine.
- LibreNMS goes into `/opt/librenms`.
- Git is installed.
- You have a [GitHub Account](https://github.com/).
- You connect to GitHub with ssh. Without ssh, replace
  `git@github.com:/yourusername/librenms.git` with
  <https://github.com/yourusername/librenms.git>.

** Replace yourusername with your GitHub username. **

#### Fork LibreNMS repo

Open [GitHub](https://github.com/librenms/librenms/fork). Then click
the 'Fork' button near the top right corner.

If you belong to several GitHub organisations, select the account of
the fork.

#### Prepare your git environment

We recommend these defaults.

```bash
git config branch.autosetupmerge true
git config --global user.name "John Doe"
git config --global user.email johndoe@example.com
```

#### Clone the repo

Clone the fork to your local install. You can then make your changes
and send them back.

```bash
cd /opt/
git clone git@github.com:/yourusername/librenms.git
```

#### Add Upstream repo

Add the master LibreNMS repository to your system. You can then pull
its changes.

```bash
git remote add upstream https://github.com/librenms/librenms.git
```

You then have two remotes:

- origin: your own repository. You can push and pull changes here.
- upstream: the main LibreNMS repository. You can only pull changes.

#### Workflow guide

You can find a better workflow for your own needs later. The workflow
below is a safe start.

Before you start a new branch or feature, update your repository.

```bash
cd /opt/librenms
git checkout master
git pull upstream master
git push origin master
```

Standard checks run at each pull request. You can run these checks
[yourself](Validating-Code.md). Your pull request then has no known
problem.

Create a new branch for your work. This step is important. You can then
work on several features at the same time and submit each one as a
separate pull request. All the work in the master branch becomes
difficult to manage.

Give your branch a name. You can use the number of an open or closed
GitHub issue. For issue number 123, use `issue-123`. You can also use
the id of a post on the community forum, such as `community-123`. Any
other name is valid. Make the name relevant to the work of the branch.

```bash
git checkout -b issue-123
```

Make your changes. Test them, change them, and test them again. Then
commit the updates for the pull request.

```bash
git add path/to/new/files/or/folders
git commit -a -m 'Added feature to do X, Y and Z'
git push origin issue-123
```

To rebase against master, run these commands:

```bash
git pull upstream master
git push origin issue-123
```

If a merge conflict occurs, correct it before you continue.

Squash all commits into one commit. This step is not essential, because
we can do it at the merge. It still helps us.

You can now submit a pull request in GitHub. Open your GitHub page of
the LibreNMS repository. Select your branch, such as `issue-123`, in
the dropdown on the left. Then click 'Pull Request'. Describe your work
in the details and click 'Create pull request'.

Thank you for your first pull request.

This guide starts you on the path of a contributor. For more questions,
join our [Discord server](https://t.libren.ms/discord).

### Hints and tips

Undo last commit

`git reset --soft 'HEAD^'`

Remove specific commit

`git revert <HASH>`

Restore deleted file

`git checkout $(git rev-list -n 1 HEAD -- "$file")^ -- "$file"`

Merge last two commits

`git rebase --interactive HEAD~2`

In the text file, change the last commit from `pick` to `squash`. Then
save the file and close it.

For more advice, read [Oh shit, git!](http://ohshitgit.com/).

[1]: http://gitready.com
[2]: http://git-scm.com/book
