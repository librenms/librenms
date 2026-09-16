# Merging Pull Requests

### GitHub

We build the monthly changelog from our GitHub commits. At each merge,
do these steps:

- Click the `Merge pull request` button
- Give the merge a descriptive but short title
- Add one of these tags to the start of the commit message. The pull
  request then appears in the changelog:
    - devices: or newdevice: new device support.
    - feature: or feat: a new or updated feature.
    - webui: or web: an update to the web interface.
    - fix: or bugfix: a bug fix.
    - refactoring: or refactor: a refactor of a large part of the code.
- You can reference an issue number with `#xyz`, i.e `#1234`
- Use the `Confirm squash and merge` button to merge.

### Example commits

#### Feature

feature: Added new availability map #4401

#### New device

newdevice: Added support for Cisco ASA #4402
