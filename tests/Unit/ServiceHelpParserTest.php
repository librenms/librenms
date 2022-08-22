<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Services\CheckParameter;
use LibreNMS\Services\HelpParser;
use LibreNMS\Tests\TestCase;
use Mockery\MockInterface;

class ServiceHelpParserTest extends TestCase
{
    /**
     * @dataProvider helpData
     *
     * @return void
     */
    public function test_example(string $check, string $help, array $expected): void
    {
        /** @var HelpParser $parser */
        $parser = $this->partialMock('\LibreNMS\Services\HelpParser[fetchHelp]', function (MockInterface $mock) use ($check, $help) {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('fetchHelp')->with($check)->once()->andReturn($help);
        });

        $actual = $parser->parse($check);

        $this->assertCount(count($expected), $actual);

        $actual = $actual->keyBy('short');
        /** @var CheckParameter $check_parameter */
        foreach ($expected as $check_parameter) {
            $this->assertEquals($check_parameter, $actual->get($check_parameter->short));
        }
    }

    public function helpData(): array
    {
        return [
            [
                'check_disk',
                <<<'EOF'
check_disk v2.3.1 (monitoring-plugins 2.3.1)
Copyright (c) 1999 Ethan Galstad <nagios@nagios.org>
Copyright (c) 1999-2008 Monitoring Plugins Development Team
        <devel@monitoring-plugins.org>

This plugin checks the amount of used disk space on a mounted file system
and generates an alert if free space is less than one of the threshold values


Usage:
 check_disk -w limit -c limit [-W limit] [-K limit] {-p path | -x device}
[-C] [-E] [-e] [-f] [-g group ] [-k] [-l] [-M] [-m] [-R path ] [-r path ]
[-t timeout] [-u unit] [-v] [-X type] [-N type]

Options:
 -h, --help
    Print detailed help screen
 -V, --version
    Print version information
 --extra-opts=[section][@file]
    Read options from an ini file. See
    https://www.monitoring-plugins.org/doc/extra-opts.html
    for usage and examples.
 -w, --warning=INTEGER
    Exit with WARNING status if less than INTEGER units of disk are free
 -w, --warning=PERCENT%
    Exit with WARNING status if less than PERCENT of disk space is free
 -c, --critical=INTEGER
    Exit with CRITICAL status if less than INTEGER units of disk are free
 -c, --critical=PERCENT%
    Exit with CRITICAL status if less than PERCENT of disk space is free
 -W, --iwarning=PERCENT%
    Exit with WARNING status if less than PERCENT of inode space is free
 -K, --icritical=PERCENT%
    Exit with CRITICAL status if less than PERCENT of inode space is free
 -p, --path=PATH, --partition=PARTITION
    Mount point or block device as emitted by the mount(8) command (may be repeated)
 -x, --exclude_device=PATH <STRING>
    Ignore device (only works if -p unspecified)
 -C, --clear
    Clear thresholds
 -E, --exact-match
    For paths or partitions specified with -p, only check for exact paths
 -e, --errors-only
    Display only devices/mountpoints with errors
 -f, --freespace-ignore-reserved
    Don't account root-reserved blocks into freespace in perfdata
 -P, --iperfdata
    Display inode usage in perfdata
 -g, --group=NAME
    Group paths. Thresholds apply to (free-)space of all partitions together
 -k, --kilobytes
    Same as '--units kB'
 -l, --local
    Only check local filesystems
 -L, --stat-remote-fs
    Only check local filesystems against thresholds. Yet call stat on remote filesystems
    to test if they are accessible (e.g. to detect Stale NFS Handles)
 -M, --mountpoint
    Display the mountpoint instead of the partition
 -m, --megabytes
    Same as '--units MB'
 -A, --all
    Explicitly select all paths. This is equivalent to -R '.*'
 -R, --eregi-path=PATH, --eregi-partition=PARTITION
    Case insensitive regular expression for path/partition (may be repeated)
 -r, --ereg-path=PATH, --ereg-partition=PARTITION
    Regular expression for path or partition (may be repeated)
 -I, --ignore-eregi-path=PATH, --ignore-eregi-partition=PARTITION
    Regular expression to ignore selected path/partition (case insensitive) (may be repeated)
 -i, --ignore-ereg-path=PATH, --ignore-ereg-partition=PARTITION
    Regular expression to ignore selected path or partition (may be repeated)
 -t, --timeout=INTEGER
    Seconds before plugin times out (default: 10)
 -u, --units=STRING
    Choose bytes, kB, MB, GB, TB (default: MB)
 -v, --verbose
    Show details for command-line debugging (output may be truncated by
    the monitoring system)
 -X, --exclude-type=TYPE
    Ignore all filesystems of indicated type (may be repeated)
 -N, --include-type=TYPE
    Check only filesystems of indicated type (may be repeated)

Examples:
 check_disk -w 10% -c 5% -p /tmp -p /var -C -w 100000 -c 50000 -p /
    Checks /tmp and /var at 10% and 5%, and / at 100MB and 50MB
 check_disk -w 100 -c 50 -C -w 1000 -c 500 -g sidDATA -r '^/oracle/SID/data.*$'
    Checks all filesystems not matching -r at 100M and 50M. The fs matching the -r regex
    are grouped which means the freespace thresholds are applied to all disks together
 check_disk -w 100 -c 50 -C -w 1000 -c 500 -p /foo -C -w 5% -c 3% -p /bar
    Checks /foo for 1000M/500M and /bar for 5/3%. All remaining volumes use 100M/50M

Send email to help@monitoring-plugins.org if you have questions regarding
use of this software. To submit patches or suggest improvements, send email
to devel@monitoring-plugins.org

EOF,
                [
                    new CheckParameter('--warning', '-w', 'INTEGER', 'Exit with WARNING status if less than INTEGER units of disk are free'),
                    new CheckParameter('--critical', '-c', 'INTEGER', 'Exit with CRITICAL status if less than INTEGER units of disk are free'),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                    new CheckParameter('--long', '', '', ''),
                ]
            ],
            [
                'check_smtp',
                <<<'EOF'
check_smtp v2.3.1 (monitoring-plugins 2.3.1)
Copyright (c) 1999-2001 Ethan Galstad <nagios@nagios.org>
Copyright (c) 2000-2007 Monitoring Plugins Development Team
        <devel@monitoring-plugins.org>

This plugin will attempt to open an SMTP connection with the host.


Usage:
check_smtp -H host [-p port] [-4|-6] [-e expect] [-C command] [-R response] [-f from addr]
[-A authtype -U authuser -P authpass] [-w warn] [-c crit] [-t timeout] [-q]
[-F fqdn] [-S] [-D warn days cert expire[,crit days cert expire]] [-v]

Options:
 -h, --help
    Print detailed help screen
 -V, --version
    Print version information
 --extra-opts=[section][@file]
    Read options from an ini file. See
    https://www.monitoring-plugins.org/doc/extra-opts.html
    for usage and examples.
 -H, --hostname=ADDRESS
    Host name, IP Address, or unix socket (must be an absolute path)
 -p, --port=INTEGER
    Port number (default: 25)
 -4, --use-ipv4
    Use IPv4 connection
 -6, --use-ipv6
    Use IPv6 connection
 -e, --expect=STRING
    String to expect in first line of server response (default: '220')
 -C, --command=STRING
    SMTP command (may be used repeatedly)
 -R, --response=STRING
    Expected response to command (may be used repeatedly)
 -f, --from=STRING
    FROM-address to include in MAIL command, required by Exchange 2000
 -F, --fqdn=STRING
    FQDN used for HELO
 -D, --certificate=INTEGER[,INTEGER]
    Minimum number of days a certificate has to be valid.
 -S, --starttls
    Use STARTTLS for the connection.
 -A, --authtype=STRING
    SMTP AUTH type to check (default none, only LOGIN supported)
 -U, --authuser=STRING
    SMTP AUTH username
 -P, --authpass=STRING
    SMTP AUTH password
 -q, --ignore-quit-failure
    Ignore failure when sending QUIT command to server
 -w, --warning=DOUBLE
    Response time to result in warning status (seconds)
 -c, --critical=DOUBLE
    Response time to result in critical status (seconds)
 -t, --timeout=INTEGER
    Seconds before connection times out (default: 10)
 -v, --verbose
    Show details for command-line debugging (output may be truncated by
    the monitoring system)

Successul connects return STATE_OK, refusals and timeouts return
STATE_CRITICAL, other errors return STATE_UNKNOWN.  Successful
connects, but incorrect response messages from the host result in
STATE_WARNING return values.

Send email to help@monitoring-plugins.org if you have questions regarding
use of this software. To submit patches or suggest improvements, send email
to devel@monitoring-plugins.org

EOF,
                [
                    new CheckParameter('--hostname', '-H', 'ADDRESS', 'Host name, IP Address, or unix socket (must be an absolute path)'),
                    new CheckParameter('--port', '-p', 'INTEGER', 'Port number (default: 25)'),
                    (new CheckParameter('--use-ipv4', '-4', '', 'Use IPv4 connection'))->setExclusiveGroup(['-4', '-6']),
                    (new CheckParameter('--use-ipv6', '-6', '', 'Use IPv6 connection'))->setExclusiveGroup(['-4', '-6']),
                    new CheckParameter('--expect', '-e', 'STRING', 'String to expect in first line of server response (default: \'220\')'),
                    new CheckParameter('--command', '-C', 'STRING', 'SMTP command (may be used repeatedly)'),
                    new CheckParameter('--response', '-R', 'STRING', 'Expected response to command (may be used repeatedly)'),
                    new CheckParameter('--from', '-f', 'STRING', 'FROM-address to include in MAIL command, required by Exchange 2000'),
                    (new CheckParameter('--authtype', '-A', 'STRING', 'SMTP AUTH type to check (default none, only LOGIN supported)'))->setInclusiveGroup(['-A', '-U', '-P']),
                    (new CheckParameter('--authuser', '-U', 'STRING', 'SMTP AUTH username'))->setInclusiveGroup(['-A', '-U', '-P']),
                    (new CheckParameter('--authpass', '-P', 'STRING', 'SMTP AUTH password'))->setInclusiveGroup(['-A', '-U', '-P']),
                    new CheckParameter('--warning', '-w', 'DOUBLE', 'Response time to result in warning status (seconds)'),
                    new CheckParameter('--critical', '-c', 'DOUBLE', 'Response time to result in critical status (seconds)'),
                    new CheckParameter('--timeout', '-t', 'INTEGER', 'Seconds before connection times out (default: 10)'),
                    new CheckParameter('--fqdn', '-F', 'STRING', 'FQDN used for HELO'),
                    new CheckParameter('--certificate', '-D', 'INTEGER[,INTEGER]', 'Minimum number of days a certificate has to be valid.'),
                    new CheckParameter('--help', '-h', '', 'Print detailed help screen'),
                    new CheckParameter('--version', '-V', '', 'Print version information'),
                    new CheckParameter('--extra-opts', '', '[section][@file]', "Read options from an ini file. See\nhttps://www.monitoring-plugins.org/doc/extra-opts.html\nfor usage and examples."),
                    new CheckParameter('--starttls', '-S', '', 'Use STARTTLS for the connection.'),
                    new CheckParameter('--ignore-quit-failure', '-q', '', 'Ignore failure when sending QUIT command to server'),
                    new CheckParameter('--verbose', '-v', '', "Show details for command-line debugging (output may be truncated by\nthe monitoring system)"),
                ],
            ],
            [
                'check_apt',
                <<<'EOF'
check_apt v2.3.1 (monitoring-plugins 2.3.1)
Copyright (c) 2006-2008 Monitoring Plugins Development Team
        <devel@monitoring-plugins.org>

This plugin checks for software updates on systems that use
package management systems based on the apt-get(8) command
found in Debian GNU/Linux


Usage:
check_apt [[-d|-u|-U]opts] [-n] [-l] [-t timeout] [-w packages-warning]

Options:
 -h, --help
    Print detailed help screen
 -V, --version
    Print version information
 --extra-opts=[section][@file]
    Read options from an ini file. See
    https://www.monitoring-plugins.org/doc/extra-opts.html
    for usage and examples.
 -t, --timeout=INTEGER
    Seconds before plugin times out (default: 10)
 -U, --upgrade=OPTS
    [Default] Perform an upgrade.  If an optional OPTS argument is provided,
    apt-get will be run with these command line options instead of the
    default (-o 'Debug::NoLocking=true' -s -qq).
    Note that you may be required to have root privileges if you do not use
    the default options.
 -d, --dist-upgrade=OPTS
    Perform a dist-upgrade instead of normal upgrade. Like with -U OPTS
    can be provided to override the default options.
 -n, --no-upgrade
    Do not run the upgrade.  Probably not useful (without -u at least).
 -l, --list
    List packages available for upgrade.  Packages are printed sorted by
    name with security packages listed first.
 -i, --include=REGEXP
    Include only packages matching REGEXP.  Can be specified multiple times
    the values will be combined together.  Any packages matching this list
    cause the plugin to return WARNING status.  Others will be ignored.
    Default is to include all packages.
 -e, --exclude=REGEXP
    Exclude packages matching REGEXP from the list of packages that would
    otherwise be included.  Can be specified multiple times; the values
    will be combined together.  Default is to exclude no packages.
 -c, --critical=REGEXP
    If the full package information of any of the upgradable packages match
    this REGEXP, the plugin will return CRITICAL status.  Can be specified
    multiple times like above.  Default is a regexp matching security
    upgrades for Debian and Ubuntu:
        ^[^\(]*\(.* (Debian-Security:|Ubuntu:[^/]*/[^-]*-security)
    Note that the package must first match the include list before its
    information is compared against the critical list.
 -o, --only-critical
    Only warn about upgrades matching the critical list.  The total number
    of upgrades will be printed, but any non-critical upgrades will not cause
    the plugin to return WARNING status.
 -w, --packages-warning
    Minumum number of packages available for upgrade to return WARNING status.
    Default is 1 package.

The following options require root privileges and should be used with care:

 -u, --update=OPTS
    First perform an 'apt-get update'.  An optional OPTS parameter overrides
    the default options.  Note: you may also need to adjust the global
    timeout (with -t) to prevent the plugin from timing out if apt-get
    upgrade is expected to take longer than the default timeout.

Send email to help@monitoring-plugins.org if you have questions regarding
use of this software. To submit patches or suggest improvements, send email
to devel@monitoring-plugins.org

EOF,
                [
                    (new CheckParameter('--dist-upgrade', '-d', 'OPTS', "Perform a dist-upgrade instead of normal upgrade. Like with -U OPTS\ncan be provided to override the default options."))->setExclusiveGroup(['-d', '-u', '-U']),
                    (new CheckParameter('--update', '-u', 'OPTS', "First perform an 'apt-get update'.  An optional OPTS parameter overrides\nthe default options.  Note: you may also need to adjust the global\ntimeout (with -t) to prevent the plugin from timing out if apt-get\nupgrade is expected to take longer than the default timeout."))->setExclusiveGroup(['-d', '-u', '-U']),
                    (new CheckParameter('--upgrade', '-U', 'OPTS', "[Default] Perform an upgrade.  If an optional OPTS argument is provided,\napt-get will be run with these command line options instead of the\ndefault (-o 'Debug::NoLocking=true' -s -qq).\nNote that you may be required to have root privileges if you do not use\nthe default options."))->setExclusiveGroup(['-d', '-u', '-U']),
                    new CheckParameter('--no-upgrade', '-n', '', 'Do not run the upgrade.  Probably not useful (without -u at least).'),
                    new CheckParameter('--list', '-l', '', "List packages available for upgrade.  Packages are printed sorted by\nname with security packages listed first."),
                    new CheckParameter('--timeout', '-t', 'INTEGER', 'Seconds before plugin times out (default: 10)'),
                    new CheckParameter('--packages-warning', '-w', '', "Minumum number of packages available for upgrade to return WARNING status.\nDefault is 1 package."),
                    new CheckParameter('--help', '-h', '', 'Print detailed help screen'),
                    new CheckParameter('--version', '-V', '', 'Print version information'),
                    new CheckParameter('--extra-opts', '', '[section][@file]', "Read options from an ini file. See\nhttps://www.monitoring-plugins.org/doc/extra-opts.html\nfor usage and examples."),
                    new CheckParameter('--include', '-i', 'REGEXP', "Include only packages matching REGEXP.  Can be specified multiple times\nthe values will be combined together.  Any packages matching this list\ncause the plugin to return WARNING status.  Others will be ignored.\nDefault is to include all packages."),
                    new CheckParameter('--exclude', '-e', 'REGEXP', "Exclude packages matching REGEXP from the list of packages that would\notherwise be included.  Can be specified multiple times; the values\nwill be combined together.  Default is to exclude no packages."),
                    new CheckParameter('--critical', '-c', 'REGEXP', "If the full package information of any of the upgradable packages match\nthis REGEXP, the plugin will return CRITICAL status.  Can be specified\nmultiple times like above.  Default is a regexp matching security\nupgrades for Debian and Ubuntu:\n^[^\(]*\(.* (Debian-Security:|Ubuntu:[^/]*/[^-]*-security)\nNote that the package must first match the include list before its\ninformation is compared against the critical list."),
                    new CheckParameter('--only-critical', '-o', '', "Only warn about upgrades matching the critical list.  The total number\nof upgrades will be printed, but any non-critical upgrades will not cause\nthe plugin to return WARNING status."),
                ],
            ]
        ];
    }
}
