#!/usr/bin/env python3
"""
Scan networks for snmp hosts and add them to LibreNMS

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

@package    LibreNMS
@link       http://librenms.org
@copyright  2017 Tony Murray
@author     Tony Murray <murraytony@gmail.com>
"""


import argparse
import json
import socket
from collections import namedtuple
from enum import Enum
from ipaddress import ip_network, ip_address
from multiprocessing import Pool
from os import path, chdir
from subprocess import check_output, CalledProcessError
from time import time

Result = namedtuple('Result', ['ip', 'hostname', 'outcome', 'output'])


class Outcome(Enum):
    UNDEFINED = 0
    ADDED = 1
    UNPINGABLE = 2
    KNOWN = 3
    FAILED = 4
    EXCLUDED = 5
    TERMINATED = 6


VERBOSE_LEVEL = 0
THREADS = 32
CONFIG = {}
EXCLUDED_NETS = []
start_time = time()
stats = {'count': 0, Outcome.ADDED: 0, Outcome.UNPINGABLE: 0, Outcome.KNOWN: 0, Outcome.FAILED: 0, Outcome.EXCLUDED: 0, Outcome.TERMINATED: 0}


def debug(message, level=2):
    if level <= VERBOSE_LEVEL:
        print(message)


def get_outcome_symbol(outcome):
    return {
        Outcome.UNDEFINED: '?',  # should not occur
        Outcome.ADDED: '+',
        Outcome.UNPINGABLE: '.',
        Outcome.KNOWN: '*',
        Outcome.FAILED: '-',
        Outcome.TERMINATED: ''
    }[outcome]


def handle_result(data):
    if VERBOSE_LEVEL > 0:
        print('Scanned \033[1m{}\033[0m {}'.format(("{} ({})".format(data.hostname, data.ip) if data.hostname else data.ip), data.output))
    else:
        print(get_outcome_symbol(data.outcome), end='', flush=True)

    stats['count'] += 0 if data.outcome == Outcome.TERMINATED else 1
    stats[data.outcome] += 1


def check_ip_excluded(ip):
    for net in EXCLUDED_NETS:
        if ip in net:
            debug("\033[91m{} excluded by autodiscovery.nets-exclude\033[0m".format(ip), 1)
            stats[Outcome.EXCLUDED] += 1
            return True
    return False


def scan_host(ip):
    hostname = None

    try:
        try:
            tmp = socket.gethostbyaddr(ip)[0]
            if socket.gethostbyname(tmp) == ip:  # check that forward resolves
                hostname = tmp
        except socket.herror:
            pass

        try:
            add_output = check_output(['/usr/bin/env', 'php', 'addhost.php', hostname or ip])
            return Result(ip, hostname, Outcome.ADDED, add_output)
        except CalledProcessError as err:
            output = err.output.decode().rstrip()
            if err.returncode == 2:
                if 'Could not ping' in output:
                    return Result(ip, hostname, Outcome.UNPINGABLE, output)
                else:
                    return Result(ip, hostname, Outcome.FAILED, output)
            elif err.returncode == 3:
                return Result(ip, hostname, Outcome.KNOWN, output)
    except KeyboardInterrupt:
        return Result(ip, hostname, Outcome.TERMINATED, 'Terminated')

    return Result(ip, hostname, Outcome.UNDEFINED, output)


if __name__ == '__main__':
    ###################
    # Parse arguments #
    ###################
    parser = argparse.ArgumentParser(description='Scan network for snmp hosts and add them to LibreNMS.')
    parser.add_argument('network', nargs='*', help="""CIDR noted IP-Range to scan. Can be specified multiple times
    This argument is only required if $config['nets'] is not set
    Example: 192.168.0.0/24
    Example: 192.168.0.0/31 will be treated as an RFC3021 p-t-p network with two addresses, 192.168.0.0 and 192.168.0.1
    Example: 192.168.0.1/32 will be treated as a single host address""")
    parser.add_argument('-t', dest='threads', type=int, help="How many IPs to scan at a time.  More will increase the scan speed, but could overload your system. Default: {}".format(THREADS))
    parser.add_argument('-l', '--legend', action='store_true', help="Print the legend.")
    parser.add_argument('-v', '--verbose', action='count', help="Show debug output. Specifying multiple times increases the verbosity.")

    # compatibility arguments
    parser.add_argument('-r', dest='network', action='append', metavar='network', help=argparse.SUPPRESS)
    parser.add_argument('-d', '-i', dest='verbose', action='count', help=argparse.SUPPRESS)
    parser.add_argument('-n', action='store_true', help=argparse.SUPPRESS)
    parser.add_argument('-b', action='store_true', help=argparse.SUPPRESS)

    args = parser.parse_args()

    VERBOSE_LEVEL = args.verbose or VERBOSE_LEVEL
    THREADS = args.threads or THREADS

    # Import LibreNMS config
    install_dir = path.dirname(path.realpath(__file__))
    chdir(install_dir)
    try:
        CONFIG = json.loads(check_output(['/usr/bin/env', 'php', 'config_to_json.php']).decode())
    except CalledProcessError as e:
        parser.error("Could not execute: {}\n{}".format(' '.join(e.cmd), e.output.decode().rstrip()))

    #######################
    # Build network lists #
    #######################
    if not CONFIG.get('nets', []) and not args.network:
        parser.error('$config[\'nets\'] is not set in config.php, you must specify a network to scan with -r')

    networks = []
    for net in (args.network if args.network else CONFIG.get('nets', [])):
        try:
            networks.append(ip_network(net, True))
            debug('Network parsed: {}'.format(net), 2)
        except ValueError as e:
            parser.error('Invalid network format {}'.format(e))

    for net in CONFIG.get('autodiscovery', {}).get('nets-exclude', {}):
        try:
            EXCLUDED_NETS.append(ip_network(net, True))
            debug('Excluded network: {}'.format(net), 2)
        except ValueError as e:
            parser.error('Invalid excluded network format {}, check your config.php'.format(e))

    #################
    # Scan networks #
    #################
    debug('SNMP settings from config.php: {}'.format(CONFIG.get('snmp', {})), 2)

    if args.legend and not VERBOSE_LEVEL:
        print('Legend:\n+  Added device\n*  Known device\n-  Failed to add device\n.  Ping failed\n')

    print('Scanning IPs:')

    pool = Pool(processes=THREADS)

    try:
        for network in networks:
            if network.num_addresses == 1:
                ips = [ip_address(network.network_address)]
            else:
                ips = network.hosts()

            for ip in ips:
                if not check_ip_excluded(ip):
                    pool.apply_async(scan_host, (str(ip),), callback=handle_result)

        pool.close()
        pool.join()
    except KeyboardInterrupt:
        pool.terminate()

    if VERBOSE_LEVEL == 0:
        print("\n")

    base = 'Scanned {} IPs: {} known devices, added {} devices, failed to add {} devices'
    summary = base.format(stats['count'], stats[Outcome.KNOWN], stats[Outcome.ADDED], stats[Outcome.FAILED])
    if stats[Outcome.EXCLUDED]:
        summary += ', {} ips excluded by config'.format(stats[Outcome.EXCLUDED])
    print(summary)
    print('Runtime: {:.2f} seconds'.format(time() - start_time))
