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
    public function test_example($check, $help, $expected)
    {
        /** @var HelpParser $parser */
        $parser = $this->partialMock(HelpParser::class, function (MockInterface $mock) use ($check, $help) {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('fetchHelp')->with($check)->once()->andReturn($help);
        });

        $actual = $parser->parse($check);

        $this->assertCount(count($expected), $actual);

        $actual = collect($actual)->keyBy('short');
        /** @var CheckParameter $check_parameter */
        foreach ($expected as $check_parameter) {
            $this->assertEquals($check_parameter, $actual->get($check_parameter->short));
        }
    }


    public function helpData(): array
    {
        return [
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
                    (new CheckParameter('--hostname', '-H', 'ADDRESS', 'Host name, IP Address, or unix socket (must be an absolute path)'))->setRequired(),
                    new CheckParameter('--port', '-p', 'INTEGER', 'Port number (default: 25)'),
                    (new CheckParameter('--use-ipv4', '-4', '', 'Use IPv4 connection'))->setExclusiveGroup(['-4', '-6']),
                    (new CheckParameter('--use-ipv6', '-6', '', 'Use IPv6 connection'))->setExclusiveGroup(['-4', '-6']),
                    new CheckParameter('--expect', '-e', 'STRING', 'String to expect in first line of server response (default: &#039;220&#039;)'),
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
                ]
            ]
        ];
    }
}
