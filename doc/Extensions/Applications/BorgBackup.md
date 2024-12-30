## BorgBackup

### SNMP Extend

1. Copy the shell script to the desired host.

```bash
wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/borgbackup -O /etc/snmp/borgbackup
```

2. Make the script executable

```bash
chmod +x /etc/snmp/borgbackup
```

3. Install depends.

=== "FreeBSD"
    ```bash
    pkg install p5-Config-Tiny p5-JSON p5-File-Slurp p5-MIME-Base64 p5-String-ShellQuote
    ```

=== "Debian/Ubuntu"
    ```bash
    apt-get install libconfig-tiny-perl libjson-perl libfile-slurp-perl libmime-base64-perl libstring-shellquote-perl
    ```

=== "Generic"
    ```bash
    cpanm Config::Tiny File::Slurp JSON MIME::Base64 String::ShellQuote
    ```

4. Set it up in cron.

``` bash
*/5 * * * /etc/snmp/borgbackup 2> /dev/null > /dev/null
```

5. Configure it. See further down below or `/etc/snmp/borgbackup --help`.

6. Add the following to the SNMPD config.

```bash
extend borgbackup /bin/cat /var/cache/borgbackup_extend/extend_return
```

7. Restart SNMPD and wait for the device to rediscover or tell it to
   manually.

#### Config

The config file is a ini file and handled by
[Config::Tiny](https://metacpan.org/pod/Config::Tiny).

```ini
- mode :: single or multi, for if this is a single repo or for
        multiple repos.
    - Default :: single

- repo :: Directory for the borg backup repo.
    - Default :: undef

- passphrase :: Passphrase for the borg backup repo.
    - Default :: undef

- passcommand :: Passcommand for the borg backup repo.
    - Default :: undef
```

For single repos all those variables are in the root section of the config,
so lets the repo is at '/backup/borg' with a passphrase of '1234abc'.

```bash
repo=/backup/borg
repo=1234abc
```

For multi, each section outside of the root represents a repo. So if
there is '/backup/borg1' with a passphrase of 'foobar' and
'/backup/derp' with a passcommand of 'pass show backup' it would be
like below.

```bash
mode=multi

[borg1]
repo=/backup/borg1
passphrase=foobar

[derp]
repo=/backup/derp
passcommand=pass show backup
```

If 'passphrase' and 'passcommand' are both specified, then passcommand
is used.

#### Metrics

The metrics are all from `.data.totals` in the extend return.

| Value                    | Type    | Description                                               |
|--------------------------|---------|-----------------------------------------------------------|
| errored                  | repos   | Total number of repos that info could not be fetched for. |
| locked                   | repos   | Total number of locked repos                              |
| locked_for               | seconds | Longest time any repo has been locked.                    |
| time_since_last_modified | seconds | Largest time - mtime for the repo nonce                   |
| total_chunks             | chunks  | Total number of chunks                                    |
| total_csize              | bytes   | Total compressed size of all archives in all repos.       |
| total_size               | byes    | Total uncompressed size of all archives in all repos.     |
| total_unique_chunks      | chunks  | Total number of unique chuckes in all repos.              |
| unique_csize             | bytes   | Total deduplicated size of all archives in all repos.     |
| unique_size              | chunks  | Total number of chunks in all repos.                      |
