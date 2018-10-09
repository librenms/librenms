#! /usr/bin/env python2
"""
 discovery-wrapper A small tool which wraps around discovery and tries to
                guide the discovery process with a more modern approach with a
                Queue and workers.

 Based on the original version of poller-wrapper.py by Job Snijders

 Author:        Neil Lathwood <neil@librenms.org>
 Date:          Sep 2016

 Usage:         This program accepts one command line argument: the number of threads
                that should run simultaneously. If no argument is given it will assume
                a default of 1 thread.

 Ubuntu Linux:  apt-get install python-mysqldb
 FreeBSD:       cd /usr/ports/*/py-MySQLdb && make install clean

 License:       This program is free software: you can redistribute it and/or modify it
                under the terms of the GNU General Public License as published by the
                Free Software Foundation, either version 3 of the License, or (at your
                option) any later version.

                This program is distributed in the hope that it will be useful, but
                WITHOUT ANY WARRANTY; without even the implied warranty of
                MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
                Public License for more details.

                You should have received a copy of the GNU General Public License along
                with this program. If not, see http://www.gnu.org/licenses/.

                LICENSE.txt contains a copy of the full GPLv3 licensing conditions.
"""
try:

    import json
    import os
    import Queue
    import subprocess
    import sys
    import threading
    import time
    from optparse import OptionParser

except:
    print "ERROR: missing one or more of the following python modules:"
    print "threading, Queue, sys, subprocess, time, os, json"
    sys.exit(2)

try:
    import MySQLdb
except:
    print "ERROR: missing the mysql python module:"
    print "On ubuntu: apt-get install python-mysqldb"
    print "On FreeBSD: cd /usr/ports/*/py-MySQLdb && make install clean"
    sys.exit(2)

"""
    Fetch configuration details from the config_to_json.php script
"""

install_dir = os.path.dirname(os.path.realpath(__file__))
config_file = install_dir + '/config.php'


def get_config_data():
    config_cmd = ['/usr/bin/env', 'php', '%s/config_to_json.php' % install_dir]
    try:
        proc = subprocess.Popen(config_cmd, stdout=subprocess.PIPE, stdin=subprocess.PIPE)
    except:
        print "ERROR: Could not execute: %s" % config_cmd
        sys.exit(2)
    return proc.communicate()[0]

try:
    with open(config_file) as f:
        pass
except IOError as e:
    print "ERROR: Oh dear... %s does not seem readable" % config_file
    sys.exit(2)

try:
    config = json.loads(get_config_data())
except:
    print "ERROR: Could not load or parse configuration, are PATHs correct?"
    sys.exit(2)

discovery_path = config['install_dir'] + '/discovery.php'
log_dir = config['log_dir']
db_username = config['db_user']
db_password = config['db_pass']
db_port = int(config['db_port'])

if config['db_socket']:
    db_server = config['db_host']
    db_socket = config['db_socket']
else:
    db_server = config['db_host']
    db_socket = None

db_dbname = config['db_name']


def db_open():
    try:
        if db_socket:
            db = MySQLdb.connect(host=db_server, unix_socket=db_socket, user=db_username, passwd=db_password, db=db_dbname)
        else:
            db = MySQLdb.connect(host=db_server, port=db_port, user=db_username, passwd=db_password, db=db_dbname)
        return db
    except:
        print "ERROR: Could not connect to MySQL database!"
        sys.exit(2)


# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC1
if 'distributed_poller_group' in config:
    discovery_group = str(config['distributed_poller_group'])
else:
    discovery_group = False


def memc_alive():
    try:
        global memc
        key = str(uuid.uuid4())
        memc.set('discovery.ping.' + key, key, 60)
        if memc.get('discovery.ping.' + key) == key:
            memc.delete('discovery.ping.' + key)
            return True
        else:
            return False
    except:
        return False


def memc_touch(key, time):
    try:
        global memc
        val = memc.get(key)
        memc.set(key, val, time)
    except:
        pass

if ('distributed_poller' in config and
    'distributed_poller_memcached_host' in config and
    'distributed_poller_memcached_port' in config and
        config['distributed_poller']):
    try:
        import memcache
        import uuid
        memc = memcache.Client([config['distributed_poller_memcached_host'] + ':' +
            str(config['distributed_poller_memcached_port'])])
        if str(memc.get("discovery.master")) == config['distributed_poller_name']:
            print "This system is already joined as the discovery master."
            sys.exit(2)
        if memc_alive():
            if memc.get("discovery.master") is None:
                print "Registered as Master"
                memc.set("discovery.master", config['distributed_poller_name'], 30)
                memc.set("discovery.nodes", 0, 3600)
                IsNode = False
            else:
                print "Registered as Node joining Master %s" % memc.get("discovery.master")
                IsNode = True
                memc.incr("discovery.nodes")
            distdisco = True
        else:
            print "Could not connect to memcached, disabling distributed discovery."
            distdisco = False
            IsNode = False
    except SystemExit:
        raise
    except ImportError:
        print "ERROR: missing memcache python module:"
        print "On deb systems: apt-get install python-memcache"
        print "On other systems: easy_install python-memcached"
        print "Disabling distributed discovery."
        distdisco = False
else:
    distdisco = False
# EOC1

s_time = time.time()
real_duration = 0
per_device_duration = {}
discovered_devices = 0

"""
    Take the amount of threads we want to run in parallel from the commandline
    if None are given or the argument was garbage, fall back to default of 1
"""
usage = "usage: %prog [options] <workers> (Default: 1 Do not set too high)"
description = "Spawn multiple discovery.php processes in parallel."
parser = OptionParser(usage=usage, description=description)
parser.add_option('-d', '--debug', action='store_true', default=False,
                  help="Enable debug output. WARNING: Leaving this enabled will consume a lot of disk space.")
(options, args) = parser.parse_args()

debug = options.debug
try:
    amount_of_workers = int(args[0])
except (IndexError, ValueError):
    amount_of_workers = 1

devices_list = []

"""
    This query specificly orders the results depending on the last_discovered_timetaken variable
    Because this way, we put the devices likely to be slow, in the top of the queue
    thus greatening our chances of completing _all_ the work in exactly the time it takes to
    discover the slowest device! cool stuff he
"""
# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC2
if discovery_group is not False:
    query = "select device_id from devices where poller_group IN(" + discovery_group + ") and disabled = 0 order by last_polled_timetaken desc"
else:
    query = "select device_id from devices where disabled = 0 order by last_polled_timetaken desc"
# EOC2


db = db_open()
cursor = db.cursor()
cursor.execute(query)
devices = cursor.fetchall()
for row in devices:
    devices_list.append(int(row[0]))
# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC3
if distdisco and not IsNode:
    query = "select max(device_id),min(device_id) from devices"
    cursor.execute(query)
    devices = cursor.fetchall()
    maxlocks = devices[0][0]
    minlocks = devices[0][1]
# EOC3
db.close()

"""
    A seperate queue and a single worker for printing information to the screen prevents
    the good old joke:

        Some people, when confronted with a problem, think,
        "I know, I'll use threads," and then they two they hav erpoblesms.
"""


def printworker():
    nodeso = 0
    while True:
# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC4
        global IsNode
        global distdisco
        if distdisco:
            if not IsNode:
                memc_touch('discovery.master', 30)
                nodes = memc.get('discovery.nodes')
                if nodes is None and not memc_alive():
                    print "WARNING: Lost Memcached. Taking over all devices. Nodes will quit shortly."
                    distdisco = False
                    nodes = nodeso
                if nodes is not nodeso:
                    print "INFO: %s Node(s) Total" % (nodes)
                    nodeso = nodes
            else:
                memc_touch('discovery.nodes', 30)
            try:
                worker_id, device_id, elapsed_time = print_queue.get(False)
            except:
                pass
                try:
                    time.sleep(1)
                except:
                    pass
                continue
        else:
            worker_id, device_id, elapsed_time = print_queue.get()
# EOC4
        global real_duration
        global per_device_duration
        global discovered_devices
        real_duration += elapsed_time
        per_device_duration[device_id] = elapsed_time
        discovered_devices += 1
        if elapsed_time < 300:
            print "INFO: worker %s finished device %s in %s seconds" % (worker_id, device_id, elapsed_time)
        else:
            print "WARNING: worker %s finished device %s in %s seconds" % (worker_id, device_id, elapsed_time)
        print_queue.task_done()

"""
    This class will fork off single instances of the discovery.php process, record
    how long it takes, and push the resulting reports to the printer queue
"""


def poll_worker():
    while True:
        device_id = poll_queue.get()
# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC5
        if not distdisco or memc.get('discovery.device.' + str(device_id)) is None:
            if distdisco:
                result = memc.add('discovery.device.' + str(device_id), config['distributed_poller_name'], 300)
                if not result:
                    print "This device (%s) appears to be being discovered by another discovery node" % (device_id)
                    poll_queue.task_done()
                    continue
                if not memc_alive() and IsNode:
                    print "Lost Memcached, Not discovering Device %s as Node. Master will discover it." % device_id
                    poll_queue.task_done()
                    continue
# EOC5
            try:
                start_time = time.time()

                output = "-d >> %s/discover_device_%s.log" % (log_dir, device_id) if debug else ">> /dev/null"
                command = "/usr/bin/env php %s -h %s %s 2>&1" % (discovery_path, device_id, output)
                subprocess.check_call(command, shell=True)

                elapsed_time = int(time.time() - start_time)
                print_queue.put([threading.current_thread().name, device_id, elapsed_time])
            except (KeyboardInterrupt, SystemExit):
                raise
            except:
                pass
        poll_queue.task_done()

poll_queue = Queue.Queue()
print_queue = Queue.Queue()

print "INFO: starting the discovery at %s with %s threads, slowest devices first" % (time.strftime("%Y-%m-%d %H:%M:%S"),
        amount_of_workers)

for device_id in devices_list:
    poll_queue.put(device_id)

for i in range(amount_of_workers):
    t = threading.Thread(target=poll_worker)
    t.setDaemon(True)
    t.start()

p = threading.Thread(target=printworker)
p.setDaemon(True)
p.start()

try:
    poll_queue.join()
    print_queue.join()
except (KeyboardInterrupt, SystemExit):
    raise

total_time = int(time.time() - s_time)

print "INFO: discovery-wrapper polled %s devices in %s seconds with %s workers" % (discovered_devices, total_time, amount_of_workers)

# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC6
if distdisco or memc_alive():
    master = memc.get("discovery.master")
    if master == config['distributed_poller_name'] and not IsNode:
        print "Wait for all discovery-nodes to finish"
        nodes = memc.get("discovery.nodes")
        while nodes > 0 and nodes is not None:
            try:
                time.sleep(1)
                nodes = memc.get("discovery.nodes")
            except:
                pass
        print "Clearing Locks"
        x = minlocks
        while x <= maxlocks:
            memc.delete('discovery.device.' + str(x))
            x = x + 1
        print "%s Locks Cleared" % x
        print "Clearing Nodes"
        memc.delete("discovery.master")
        memc.delete("discovery.nodes")
    else:
        memc.decr("discovery.nodes")
    print "Finished %s." % time.time()
# EOC6

show_stopper = False

if total_time > 21600:
    print "WARNING: the process took more than 6 hours to finish, you need faster hardware or more threads"
    print "INFO: in sequential style discovery the elapsed time would have been: %s seconds" % real_duration
    for device in per_device_duration:
        if per_device_duration[device] > 3600:
            print "WARNING: device %s is taking too long: %s seconds" % (device, per_device_duration[device])
            show_stopper = True
    if show_stopper:
        print "ERROR: Some devices are taking more than 3600 seconds, the script cannot recommend you what to do."
    else:
        recommend = int(total_time / 300.0 * amount_of_workers + 1)
        print "WARNING: Consider setting a minimum of %d threads. (This does not constitute professional advice!)" % recommend

    sys.exit(2)
