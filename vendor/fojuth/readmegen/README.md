# ReadmeGen

[![Build Status](https://travis-ci.org/fojuth/readmegen.svg)](https://travis-ci.org/fojuth/readmegen)

Generate your project's log using VCS commit messages.

ReadmeGen is a PHP package that scans the VCS's log searching for messages with specific pattern. These messages are extracted, grouped and prepended to the changelog file (e.g. readme.md). The package can be instructed to fetch messages between specific tags (or commits). This way, whenever you're tagging a new release, you can run ReadmeGen to generate the changelog automatically.

**Notice**: The package currently supports only GIT and the *.md output files. You can provide support for othe VCS's or output formats? Help welcome :)

### Installation
#### Global installation (recommended)
```
composer global require fojuth/readmegen:@stable
```

You can read more about global installation in the [composer docs](https://getcomposer.org/doc/03-cli.md#global).

#### Local installation
```
composer require fojuth/readmegen:1.*
```

#### Windows installation
Make sure the Windows `PATH` variable contains the path to the composer bin dir:
```
C:\Users\{USER}\AppData\Roaming\Composer\vendor\bin
```
Restart any shell terminal you want to use for changes to take effect.

### Usage
This package is intended to be used as an executable script, not a library you would include in your project. Assuming you installed ReadmeGen globally, to update your changelog file, simply run:

```
readmegen --from TAG --to TAG --release RELEASE_NUMBER --break BREAKPOINT
```

For example:
```
readmegen --from 1.12.0 --to 1.13.0 --release 1.13.0 --break *Changelog*
```

This tells the script to generate a changelod update named `1.13.0` and that it should scan the log since tag `1.12.0` up to `1.13.0`. No earlier (or latter) commits will be taken into consideration. ReadmeGen will inject the generated log *after* the `*Changelog*` line.

If you want to generate the changelog from a specific tag (or commit checksum) up to the latest commit (`HEAD`) just omit the `--to` argument:
```
readmegen --from a04cf99 --release 1.13.0 --break *Changelog*
```

You can also specify the breakpoint in the `readmegen.yml` config file so the command will be even cleaner:
```
readmegen --from a04cf99 --release 1.13.0
```

### Message format
ReadmeGen will search for messages that start with a specific keyword. These keywords tell the script to which group the commit should be appended. The message groups can be overwritten.

For example - the default configuration supports four types of commits: Features, Bugfixes, Documentation and Refactoring. The commit will be appended to a certain group only if it starts with a specific word. The default config allows two keywords for bugfixes: `bugfix` and `fix`. This means, that for a message to be appended to the Bugfix group it has to start with either `bugfix: blabla` or `Fix: foo bar` (notice the colon `:` sign - it has to be right after the keyword). The keywords are case insensitive.

All commits that do not fit into any of the groups will be ignored (we don't want merges and stuff like that in the changelog).

### Grouping commits
Each commit that fits into a group will be grouped (yeah, that sounds silly). Groups will be printed out in the order they appear in the config file, so if you have `Features` and `Bugfixes`, this is the order they will appear in the changelog:
```
Features
- feature 1
- feature 2

Bugfixes
- fix 1
```

You can override the groups in your custom config file (details below).

### Link patterns
ReadmeGen can link issues to a issue tracker - all numbers starting with `#` will be linked to a website defined in the config under the `issue_tracker_pattern` key. If a commit message has a string `#1234` in it, it will be converted to a link targeting the issue tracker.

### Local config
The default config holds the definitions of commit groups and the issue link pattern. It also specifies which VCS to use and the type of the output file. You can override these settings (project-wide) by creating a `readmegen.yml` file in the root dir of your project. When ReadmeGen will be run it will check if this file exists and merge the settings accordingly.

The default `readmegen.yml` config looks like this:
```
vcs: git
format: md
issue_tracker_pattern: http://some.issue.tracker.com/\1
break: "## Changelog"
output_file_name: "README.md"
message_groups:
  Features:
    - feature
    - feat
  Bugfixes:
    - fix
    - bugfix
  Documentation:
    - docs
  Refactoring:
    - refactoring
```

Each of the `message_groups` key is the name of the group that will be put in the changelog. The values inside the group  are the keywords the commit must start with (followed by the colon `:` sign) to be appended to that group.

### Release number
ReadmeGen requires a release number (`--release`) to be provided. This will be the title of the generated changelog.

### Breakpoint
By default the changes will go onto the beginning of the changelog file. You can though specify a "breakpoint" beneath which these changes should be appended. Usually, you'll have some "intro" in you changelog, and the changes listed below. You don't want the script to push the changes on top of the file, but below a certain line. You can specify this line in the `readmegen.yml` config file or using the `--break` argument.

For example:
```
readmegen --from 1.12.0 --to 1.13.0 --release 1.3.3 --break *Changelog*
```
The script will append the changes *below* the line that contains the `*Changelog*` phrase. This should be the only phrase in this line. If you use the CLI argument method (`--break`), the breakpoint **must not contain spaces**. Thus you are encouraged to use the config method - you can use spaces there, as shown in the default config.

ReadmeGen will search for the `## Changelog` breakpoint by default. If the breakpoint phrase is not found, the output will go onto the beginning of the changelog file.

### Example commits
Here are some example commit messages that will be grabbed by ReadmeGen (with the default config):
```
feature: Added some cool stuff (#1234)
fix: #4245, regarding client login bug
docs: Updated the transaction section of the docs
feat: Some more cool stuff
```

## Changelog
## 1.1.2
*(2015-07-12)*

#### Features
* Change output file name (thanks to [reva2](https://github.com/reva2))

#### Bugfixes
* Added missing new line character in example usage message (thanks to [reva2](https://github.com/reva2))

---

## 1.1.1
*(2015-01-04)*

#### Features
* Added .travis.yml

---

## 1.1.0
*(2014-12-30)*

#### Features
* Added "break" to the readmegen.yml default config file. It has a default value set and can be overwritten locally.

---

## 1.0.2
*(2014-12-30)*

#### Bugfixes
* The release date is extracted from the --to commit.

---
