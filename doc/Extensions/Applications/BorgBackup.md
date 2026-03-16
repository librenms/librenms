# BorgBackup

Monitor Borg repositories via a LibreNMS JSON SNMP extend.

The script uses `borg info <repo> --json` to collect stats and writes cached output files for `snmpd` to read.

## SNMP Extend

1. Download the Perl script onto the host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/borgbackup -O /etc/snmp/borgbackup
    ```

2. Make the script executable.

    ```bash
    chmod +x /etc/snmp/borgbackup
    ```

3. Install dependencies.

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

4. Create a config file.

    Default config path is `/usr/local/etc/borgbackup_extend.ini` (override with `-c`).

5. Run it periodically (writes output files, then SNMP only reads them).

    Default output dir is `/var/cache/borgbackup_extend` (override with `-o`).

    === "cron"
        
        Add to `/etc/cron.d/librenms-borgbackup-extend`:

        ```bash
        */5 * * * * /etc/snmp/borgbackup -c /usr/local/etc/borgbackup_extend.ini -o /var/cache/borgbackup_extend >/dev/null 2>&1
        ```

    === "systemd"
        Create a dedicated user (adjust as needed):

        === "Debian/Ubuntu"
            ```bash 
            adduser --system --group --home /var/lib/borgbackup --shell /usr/sbin/nologin borgbackup
            ```

        === "RHEL/CentOS"
            ```bash
            groupadd --system borgbackup 2>/dev/null || true
            useradd --system --gid borgbackup --home-dir /var/lib/borgbackup --shell /usr/sbin/nologin borgbackup
            ```

        `/etc/systemd/system/borgbackup-extend.service`

        ```ini
        [Unit]
        Description=LibreNMS BorgBackup SNMP extend cache

        [Service]
        Type=oneshot
        # Run as a user that can read the borg repo(s)
        User=borgbackup
        Group=borgbackup
        ExecStart=/etc/snmp/borgbackup -c /usr/local/etc/borgbackup_extend.ini -o /var/cache/borgbackup_extend
        ```

        `/etc/systemd/system/borgbackup-extend.timer`

        ```ini
        [Unit]
        Description=Run LibreNMS BorgBackup extend every 5 minutes

        [Timer]
        OnBootSec=2min
        OnUnitActiveSec=5min
        Persistent=true

        [Install]
        WantedBy=timers.target
        ```

        Enable the timer:

        ```bash
        systemctl daemon-reload
        systemctl enable --now borgbackup-extend.timer
        ```

        Adjust `User=`/`Group=` and ensure `/var/cache/borgbackup_extend` is writable by that user and readable by `snmpd`.

6. Add the following to `snmpd.conf`.

    ```bash
    extend borgbackup /bin/cat /var/cache/borgbackup_extend/extend_return
    ```

7. Restart `snmpd` and wait for the device to rediscover (or rediscover it manually).

## Notes

- Run the periodic job (cron/systemd) as a user that can access the Borg repo(s); `snmpd` only needs read access to the output dir.
- `repo=` must be a local filesystem path to the repo directory (the script reads `$repo/nonce` and `$repo/lock.exclusive`).
- The script writes two files under the output dir: `extend_return` (used by SNMP) and `pretty` (human-readable JSON for debugging).
- `extend_return` may be plain JSON or gzip+base64 compressed JSON (the script auto-selects the smaller format).
- Caching to files avoids running `borg info ...` as `snmpd` and avoids long runtimes (lock timeouts can cause slow polls on large repos).

## Flags

- `-c <path>`: config file (default: `/usr/local/etc/borgbackup_extend.ini`)
- `-o <path>`: output directory (default: `/var/cache/borgbackup_extend`)
- `-h|--help`: help
- `-v|--version`: version

## Config

The config file is an INI file and handled by
[Config::Tiny](https://metacpan.org/pod/Config::Tiny).

Keys:

- `mode`: `single` or `multi` (default: `single`)
- `repo`: filesystem path to the Borg repo directory
- `passphrase`: passphrase for the repo
- `passcommand`: passcommand for the repo

If `passphrase` and `passcommand` are both specified, `passcommand` is used.

!!! note
    Unencrypted repositories: the current script requires either `passphrase` or `passcommand` to be set, even if `borg info` works without credentials. If you use an unencrypted repo, set `passphrase` to a something (example: `passphrase=0`).

### Single repo example

All variables are in the root section:

```ini
repo=/backup/borg
passphrase=1234abc
```

### Multi repo example

Each section outside of the root represents a repo:

```ini
mode=multi

[borg1]
repo=/backup/borg1
passphrase=foobar

[derp]
repo=/backup/derp
passcommand=pass show backup
```

## Metrics

Totals are from `.data.totals` in the extend return.

| Value                    | Type    | Description                                               |
|--------------------------|---------|-----------------------------------------------------------|
| errored                  | repos   | Total number of repos that info could not be fetched for. |
| locked                   | repos   | Total number of locked repos.                             |
| locked_for               | seconds | Longest time any repo has been locked.                    |
| time_since_last_modified | seconds | Largest `time - mtime($repo/nonce)` across repos.         |
| total_chunks             | chunks  | Total number of chunks.                                   |
| total_csize              | bytes   | Total compressed size of all archives in all repos.       |
| total_size               | bytes   | Total uncompressed size of all archives in all repos.     |
| total_unique_chunks      | chunks  | Total number of unique chunks in all repos.               |
| unique_csize             | bytes   | Total deduplicated compressed size in all repos.          |
| unique_size              | bytes   | Total deduplicated uncompressed size in all repos.        |

Per-repo values are under `.data.repos.<name>` (in `single` mode the repo key is `single`).

| Value                    | Type    | Description                                  |
|--------------------------|---------|----------------------------------------------|
| error                    | string  | Error returned when fetching info (if any).  |
| locked                   | 0/1     | Repo is locked.                              |
| locked_for               | seconds | Time since `lock.exclusive` creation.        |
| time_since_last_modified | seconds | `time - mtime($repo/nonce)`.                 |
| total_chunks             | chunks  | Repo total chunks.                           |
| total_csize              | bytes   | Repo total compressed size.                  |
| total_size               | bytes   | Repo total uncompressed size.                |
| total_unique_chunks      | chunks  | Repo unique chunk count.                     |
| unique_csize             | bytes   | Repo deduplicated compressed size.           |
| unique_size              | bytes   | Repo deduplicated uncompressed size.         |

## JSON Return

The extend output follows the LibreNMS JSON SNMP extend format:
`https://docs.librenms.org/Developing/Application-Notes/#librenms-json-snmp-extends`

Top-level keys:

- `version`: JSON extend version (currently `1`)
- `error`: exit/error code (also used as the script exit status)
- `errorString`: error details (when `error != 0`)
- `data`: payload (includes `mode`, `totals`, and `repos`)

Common error codes:

- `1`: failed reading config file
- `2`: `mode` is not `single` or `multi`
- `3`: neither `passphrase` nor `passcommand` defined
- `4`: `repo` is not defined
