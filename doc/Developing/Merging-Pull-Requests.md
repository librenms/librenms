source: Developing/Merging-Pull-Requests.md
path: blob/master/doc/

# Merging Pull Requests

### GitHub

We will now build the monthly change log from our GitHub commits. When
merging a commit, please  ensure you:

- Click the `Merge pull request` button
- Give the merge a descriptive but short title
- For the commit message prepend it with one of the following tags for
  the pull request to appear in the changelog:
  - devices: or newdevice: For new device support.
  - feature: or feat: To indicate this is a new or updated feature
  - webui: or web: To indicate this is an update to the WebUI
  - fix: or bugfix: To show this is a bug fix.
  - refactoring: or refactor: When the changes are refactoring a large
    portion of code
- You can reference an issue number with `#xyz`, i.e `#1234`
- Use the `Confirm squash and merge` button to merge.

### Example commits

#### Feature

feature: Added new availability map #4401

#### New device

newdevice: Added support for Cisco ASA #4402
