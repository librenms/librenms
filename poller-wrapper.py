#! /usr/bin/env python
"""
 poller-wrapper A small tool which wraps around the poller and tries to
                guide the polling process with a more modern approach with a
                Queue and workers

 Author:        Job Snijders <job.snijders@atrato.com>
 Date:          Jan 2013

 Usage:         This program accepts one command line argument: the number of threads
                that should run simultaneously. If no argument is given it will assume
                a default of 16 threads.

 Read more:     http://postman.memetic.org/pipermail/observium/2012-November/001303.html

 Ubuntu Linux:  apt-get install python-mysqldb
 FreeBSD:       cd /usr/ports/*/py-MySQLdb && make install clean

 Tested on:     Python 2.7.3 / PHP 5.3.10-1ubuntu3.4 / Ubuntu 12.04 LTS

 GitHub:        https://github.com/Atrato/observium-poller-wrapper

 License:       To the extent possible under law, Job Snijders has waived all
                copyright and related or neighboring rights to this script.
                This script has been put into the Public Domain. This work is
                published from: The Netherlands.
"""
try:

    import json
    import os
    import Queue
    import subprocess
    import sys
    import threading
    import time

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

ob_install_dir = os.path.dirname(os.path.realpath(__file__))
config_file = ob_install_dir + '/config.php'


def get_config_data():
    config_cmd = ['/usr/bin/env', 'php', '%s/config_to_json.php' % ob_install_dir]
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

poller_path = config['install_dir'] + '/poller.php'
db_username = config['db_user']
db_password = config['db_pass']

if config['db_host'][:5].lower() == 'unix:':
    db_server = config['db_host']
    db_port = 0
elif ':' in config['db_host']:
    db_server = config['db_host'].rsplit(':')[0]
    db_port = int(config['db_host'].rsplit(':')[1])
else:
    db_server = config['db_host']
    db_port = 0

db_dbname = config['db_name']


def db_open():
    try:
        if db_port == 0:
            db = MySQLdb.connect(host=db_server, user=db_username, passwd=db_password, db=db_dbname)
        else:
            db = MySQLdb.connect(host=db_server, port=db_port, user=db_username, passwd=db_password, db=db_dbname)
        return db
    except:
        print "ERROR: Could not connect to MySQL database!"
        sys.exit(2)


# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC1
if 'distributed_poller_group' in config:
    poller_group = str(config['distributed_poller_group'])
else:
    poller_group = False


def memc_alive():
    try:
        global memc
        key = str(uuid.uuid4())
        memc.set('poller.ping.' + key, key, 60)
        if memc.get('poller.ping.' + key) == key:
            memc.delete('poller.ping.' + key)
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
        if str(memc.get("poller.master")) == config['distributed_poller_name']:
            print "This sytem is already joined as the poller master."
            sys.exit(2)
        if memc_alive():
            if memc.get("poller.master") is None:
                print "Registered as Master"
                memc.set("poller.master", config['distributed_poller_name'], 10)
                memc.set("poller.nodes", 0, 300)
                IsNode = False
            else:
                print "Registered as Node joining Master %s" % memc.get("poller.master")
                IsNode = True
                memc.incr("poller.nodes")
            distpoll = True
        else:
            print "Could not connect to memcached, disabling distributed poller."
            distpoll = False
            IsNode = False
    except ImportError:
        print "ERROR: missing memcache python module:"
        print "On deb systems: apt-get install python-memcache"
        print "On other systems: easy_install python-memcached"
        print "Disabling distributed poller."
        distpoll = False
else:
    distpoll = False
# EOC1

s_time = time.time()
real_duration = 0
per_device_duration = {}
polled_devices = 0

"""
    Take the amount of threads we want to run in parallel from the commandline
    if None are given or the argument was garbage, fall back to default of 16
"""
try:
    amount_of_workers = int(sys.argv[1])
    if amount_of_workers == 0:
        print "ERROR: 0 threads is not a valid value"
        sys.exit(2)
except:
    amount_of_workers = 16

devices_list = []

"""
    This query specificly orders the results depending on the last_polled_timetaken variable
    Because this way, we put the devices likely to be slow, in the top of the queue
    thus greatening our chances of completing _all_ the work in exactly the time it takes to
    poll the slowest device! cool stuff he
"""
# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC2
if poller_group is not False:
    query = "select device_id from devices where poller_group IN(" + poller_group + ") and disabled = 0 order by last_polled_timetaken desc"
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
if distpoll and not IsNode:
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
        "I know, I'll use threads," and then two they hav erpoblesms.
"""


def printworker():
    nodeso = 0
    while True:
# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC4
        global IsNode
        global distpoll
        if distpoll:
            if not IsNode:
                memc_touch('poller.master', 10)
                nodes = memc.get('poller.nodes')
                if nodes is None and not memc_alive():
                    print "WARNING: Lost Memcached. Taking over all devices. Nodes will quit shortly."
                    distpoll = False
                    nodes = nodeso
                if nodes is not nodeso:
                    print "INFO: %s Node(s) Total" % (nodes)
                    nodeso = nodes
            else:
                memc_touch('poller.nodes', 10)
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
        global polled_devices
        real_duration += elapsed_time
        per_device_duration[device_id] = elapsed_time
        polled_devices += 1
        if elapsed_time < 300:
            print "INFO: worker %s finished device %s in %s seconds" % (worker_id, device_id, elapsed_time)
        else:
            print "WARNING: worker %s finished device %s in %s seconds" % (worker_id, device_id, elapsed_time)
        print_queue.task_done()

"""
    This class will fork off single instances of the poller.php process, record
    how long it takes, and push the resulting reports to the printer queue
"""


def poll_worker():
    while True:
        device_id = poll_queue.get()
# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC5
        if not distpoll or memc.get('poller.device.' + str(device_id)) is None:
            if distpoll:
                result = memc.add('poller.device.' + str(device_id), config['distributed_poller_name'], 300)
                if not result:
                    print "This device (%s) appears to be being polled by another poller" % (device_id)
                    poll_queue.task_done()
                    continue
                if not memc_alive() and IsNode:
                    print "Lost Memcached, Not polling Device %s as Node. Master will poll it." % device_id
                    poll_queue.task_done()
                    continue
# EOC5
            try:
                start_time = time.time()
                command = "/usr/bin/env php %s -h %s >> /dev/null 2>&1" % (poller_path, device_id)
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

print "INFO: starting the poller at %s with %s threads, slowest devices first" % (time.strftime("%Y-%m-%d %H:%M:%S"),
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

print "INFO: poller-wrapper polled %s devices in %s seconds with %s workers" % (polled_devices, total_time, amount_of_workers)

# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC6
if distpoll or memc_alive():
    master = memc.get("poller.master")
    if master == config['distributed_poller_name'] and not IsNode:
        print "Wait for all poller-nodes to finish"
        nodes = memc.get("poller.nodes")
        while nodes > 0 and nodes is not None:
            try:
                time.sleep(1)
                nodes = memc.get("poller.nodes")
            except:
                pass
        print "Clearing Locks"
        x = minlocks
        while x <= maxlocks:
            memc.delete('poller.device.' + str(x))
            x = x + 1
        print "%s Locks Cleared" % x
        print "Clearing Nodes"
        memc.delete("poller.master")
        memc.delete("poller.nodes")
    else:
        memc.decr("poller.nodes")
    print "Finished %s." % time.time()
# EOC6

show_stopper = False

db = db_open()
cursor = db.cursor()
query = "update pollers set last_polled=NOW(), devices='%d', time_taken='%d' where poller_name='%s'" % (polled_devices,
        total_time, config['distributed_poller_name'])
response = cursor.execute(query)
if response == 1:
    db.commit()
else:
    query = "insert into pollers set poller_name='%s', last_polled=NOW(), devices='%d', time_taken='%d'" % (
            config['distributed_poller_name'], polled_devices, total_time)
    cursor.execute(query)
    db.commit()
db.close()


if total_time > 300:
    print "WARNING: the process took more than 5 minutes to finish, you need faster hardware or more threads"
    print "INFO: in sequential style polling the elapsed time would have been: %s seconds" % real_duration
    for device in per_device_duration:
        if per_device_duration[device] > 300:
            print "WARNING: device %s is taking too long: %s seconds" % (device, per_device_duration[device])
            show_stopper = True
    if show_stopper:
        print "ERROR: Some devices are taking more than 300 seconds, the script cannot recommend you what to do."
    else:
        recommend = int(total_time / 300.0 * amount_of_workers + 1)
        print "WARNING: Consider setting a minimum of %d threads. (This does not constitute professional advice!)" % recommend

    sys.exit(2)
