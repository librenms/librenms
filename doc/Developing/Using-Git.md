source: Developing/Using-Git.md
path: blob/master/doc/

Git can have a bit of a steep learning curve, stick with it as it is
worth learning the [basics][1][2] at least.

If you want to help develop LibreNMS and haven't really used Git
before then this quick primer will help you get started.

Some assumptions:

- Work is being done on a Linux box.
- LibreNMS is to be installed in /opt/librenms
- You have git installed.
- You have a [GitHub Account](https://github.com/).
- You are using ssh to connect to GitHub (If not, replace
  git@github.com:/yourusername/librenms.git with <https://github.com/yourusername/librenms.git>.

** Replace yourusername with your GitHub username. **

#### Fork LibreNMS repo

You do this directly within
[GitHub](https://github.com/librenms/librenms/fork), click the 'Fork'
button near the top right.

If you are associated with multiple organisations within GitHub then
you might need to select which account you want to push the fork to.

#### Prepare your git environment

These are the defaults that are recommended.

```bash
git config branch.autosetupmerge true
git config --global user.name "John Doe"
git config --global user.email johndoe@example.com
```

#### Clone the repo

Ok so now that you have forked the repo, you now need to clone it to
your local install where you can then make the changes you need and submit them back.

```bash
cd /opt/
git clone git@github.com:/yourusername/librenms.git
```

#### Add Upstream repo

To be able to pull in changes from the master LibreNMS repo you need
to have it setup on your system.

```bash
git remote add upstream https://github.com/librenms/librenms.git
```

Now you have two configured remotes:

- origin: This is your repository, you can push and pull changes here.
- upstream: This is the main LibreNMS repository and you can only pull changes.

#### Workflow guide

As you become more familiar you may find a better workflow that fits
your needs, until then this should be a safe workflow for you to follow.

Before you start work on a new branch / feature. Make sure you are up
to date.

```bash
cd /opt/librenms
git checkout master
git pull upstream master
git push origin master
```

At this stage it's worth pointing out that we have some standard
checks that are performed when you submit a pull request, you can run
these checks [yourself](Validating-Code.md) to be sure no issues are
present in your  pull request.

Now, create a new branch to do you work on. It's important that you do
this as you are then able to work on more than one feature at a time
and submit them as pull requests individually. If you did all your
work in the master branch then it gets a bit messy!

You need to give your branch a name. If an issue is open (or closed on
GitHub) then you can use that, in this example if the issue number is
123 then we will use issue-123. If a post exists on the community
forum then you can use the post id like community-123. You're also
welcome to use any arbitrary name for your branch but try and make it
relevant to what the branch is.

```bash
git checkout -b issue-123
```

Now, code away. Make the changes you need, test, change and test again
:) When you are ready to submit the updates as a pull request then commit away.

```bash
git add path/to/new/files/or/folders
git commit -a -m 'Added feature to do X, Y and Z'
git push origin issue-123
```

If you need to rebase against master then you can do this with:

```bash
git pull upstream master
git push origin issue-123
```

If after do this you get some merge conflicts then you need to resolve
these before carrying on.

Please try to squash all commits into one, this isn't essential as we
can do this when we merge but it would be helpful to do this before
you submit your pull request.

Now you will be ready to submit a pull request from within GitHub. To
do this, go to your GitHub page for the LibreNMS repo. Now select the
branch you have just been working on (issue-123) from the drop down to
the left and then click 'Pull Request'. Fill in the details to
describe the work you have done and click 'Create pull request'.

Thanks for your first pull request :)

Ok, that should get you started on the contributing path. If you have
any other questions then stop by our [Discord Server](https://t.libren.ms/discord)

### Hints and tips

Undo last commit

`git reset --soft 'HEAD^'`

Remove specific commit

`git revert <HASH>`

Restore deleted file

`git checkout $(git rev-list -n 1 HEAD -- "$file")^ -- "$file"`

Merge last two commits

`git rebase --interactive HEAD~2`

In the text file that opens, change the last commit to squash from
pick then save an exit.

For more tips take a look at [Oh shit, git!](http://ohshitgit.com/)

[1]: http://gitready.com
[2]: http://git-scm.com/book
