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
from ipaddress import ip_network
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

VERBOSITY = 0
THREADS = 32
start_time = time()
stats = {'count': 0, Outcome.ADDED: 0, Outcome.UNPINGABLE: 0, Outcome.KNOWN: 0, Outcome.FAILED: 0}


def get_outcome_symbol(outcome):
    return {
        Outcome.UNDEFINED: '?',  # should not occur
        Outcome.ADDED: '+',
        Outcome.UNPINGABLE: '.',
        Outcome.KNOWN: '*',
        Outcome.FAILED: '-',
    }[outcome]


def handle_result(data):
    if VERBOSITY > 0:
        print('Scanned {}: {}'.format(("{} ({})".format(data.hostname, data.ip) if data.hostname else data.ip), data.output))
    else:
        print(get_outcome_symbol(data.outcome), end='', flush=True)

    stats['count'] += 1
    stats[data.outcome] += 1


def scan_host(ip):
    hostname = None

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
        output = err.output.decode('utf-8').rstrip()
        if err.returncode == 2:
            if 'Could not ping' in output:
                return Result(ip, hostname, Outcome.UNPINGABLE, output)
            else:
                return Result(ip, hostname, Outcome.FAILED, output)
        elif err.returncode == 3:
            return Result(ip, hostname, Outcome.KNOWN, output)

        return Result(ip, hostname, Outcome.UNDEFINED, output)

if __name__ == '__main__':
    ###################
    # Parse arguments #
    ###################
    parser = argparse.ArgumentParser(description='Scan network for snmp hosts and add them to LibreNMS.')
    parser.add_argument('-r', action='append', metavar='NETWORK', help="""CIDR noted IP-Range to scan. Can be specified multiple times
    This argument is only required if $config['nets'] is not set
    Example: 192.168.0.0/24
    Example: 192.168.0.0/31 will be treated as an RFC3021 p-t-p network with two addresses, 192.168.0.0 and 192.168.0.1
    Example: 192.168.0.1/32 will be treated as a single host address""")
    parser.add_argument('-t', dest='threads', type=int, help="How many IPs to scan at a time.  More will increase the scan speed, but could overload your system. Default: {}".format(THREADS))
    parser.add_argument('-l', '--legend', action='store_true', help="Print the legend.")
    parser.add_argument('-v', '--verbose', action='count', help="Show debug output. Specifying multiple times increases the verbosity.")
    args = parser.parse_args()

    VERBOSITY = args.verbose or VERBOSITY
    THREADS = args.threads or THREADS

    # Collect networks
    install_dir = path.dirname(path.realpath(__file__))
    chdir(install_dir)
    config = json.loads(check_output(['/usr/bin/env', 'php', 'config_to_json.php']).decode('utf-8'))

    if not config.get('nets', []) and not args.r:
        parser.error('$config[\'nets\'] is not set in config.php, you must specify a network to scan with -r')

    networks = []
    for net in (args.r if args.r else config.get('nets', [])):
        if net:
            networks.append(ip_network(net))

    #################
    # Scan networks #
    #################
    if args.legend:
        print("Legend:\n+  Added device\n*  Known device\n-  Failed to add device\n.  Ping failed\n")

    print('Scanning IPs:')

    pool = Pool(processes=THREADS)              # start 4 worker processes

    for network in networks:
        if str(network).endswith('/32'):
            ip = str(network)[:-3]
            pool.apply_async(scan_host, (ip,), callback=handle_result)

        for ip in network.hosts():
            ip = str(ip)
            pool.apply_async(scan_host, (ip,), callback=handle_result)

    pool.close()
    pool.join()

    if VERBOSITY == 0:
        print("\n")

    print('Scanned {} IPs: Already know {} devices, Added {} devices, Failed to add {} devices'.format(stats['count'], stats[Outcome.KNOWN], stats[Outcome.ADDED], stats[Outcome.FAILED]))
    print('Runtime: {:.2f} seconds'.format(time() - start_time))
