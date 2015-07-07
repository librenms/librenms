#! /usr/bin/env python
"""
 poller-service A service to wrap SNMP polling.  It will poll up to $threads devices at a time, and will not re-poll
                devices that have been polled within the last $frequency seconds. It will prioritize devices based on
                the last time polled. If resources are sufficient, this service should poll every device every
                $frequency seconds, but should gracefully degrade if resources are inefficient, polling devices as
                frequently as possible. This service is based on poller-wrapper.py.

 Author:        Clint Armstrong <clint@clintarmstrong.net>
 Date:          July 2015

 Usage:         poller-service [threads] [frequency]
                Default is 16 threads and 300 seconds.
"""

import json
import os
import Queue
import subprocess
import sys
import threading
import time
import MySQLdb
from datetime import datetime, timedelta

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

s_time = time.time()
real_duration = 0
per_device_duration = {}
polled_devices = 0

# Take the amount of threads we want to run in parallel from the commandline
# if None are given or the argument was garbage, fall back to default of 16
try:
    amount_of_workers = int(sys.argv[1])
    if amount_of_workers == 0:
        print "ERROR: 0 threads is not a valid value"
        sys.exit(2)
except:
    amount_of_workers = 16

# Take the frequency of scans we want from the commandline
# if None are given or the argument was garbage, fall back to default of 300
try:
    frequency = int(sys.argv[2])
    if frequency == 0:
        print "ERROR: 0 seconds is not a valid value"
        sys.exit(2)
except:
    frequency = 300

# Take the down_retry value from the commandline
# if None are given or the argument was garbage, fall back to default of 15
try:
    down_retry = int(sys.argv[3])
    if down_retry == 0:
        print "ERROR: 0 seconds is not a valid value"
        sys.exit(2)
except:
    down_retry = 15

try:
    if db_port == 0:
        db = MySQLdb.connect(host=db_server, user=db_username, passwd=db_password, db=db_dbname)
    else:
        db = MySQLdb.connect(host=db_server, port=db_port, user=db_username, passwd=db_password, db=db_dbname)
    db.autocommit(True)
    cursor = db.cursor()
except:
    print "ERROR: Could not connect to MySQL database!"
    sys.exit(2)

# A seperate queue and a single worker for printing information to the screen prevents
# the good old joke:
#
#     Some people, when confronted with a problem, think,
#     "I know, I'll use threads," and then two they hav erpoblesms.


def printworker():
    nodeso = 0
    while True:
        worker_id, device_id, elapsed_time = print_queue.get()
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


def poll_worker(device_id):
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

print_queue = Queue.Queue()

print "INFO: starting the poller at %s with %s threads" % (time.strftime("%Y-%m-%d %H:%M:%S"), amount_of_workers)

p = threading.Thread(target=printworker)
p.setDaemon(True)
p.start()


def lockFree(lock):
    global cursor
    query = "SELECT IS_FREE_LOCK('{}')".format(lock)
    cursor.execute(query)
    return cursor.fetchall()[0][0] == 1


def getLock(lock):
    global cursor
    query = "SELECT GET_LOCK('{}', 0)".format(lock)
    cursor.execute(query)
    return cursor.fetchall()[0][0] == 1


def releaseLock(lock):
    global cursor
    query = "SELECT RELEASE_LOCK('{}')".format(lock)
    cursor.execute(query)
    return cursor.fetchall()[0][0] == 1

def sleep_until(timestamp):
    now = datetime.now()
    if timestamp > now:
        sleeptime = (timestamp - now).seconds
    else:
        sleeptime = 0
    time.sleep(sleeptime)

poller_group = ('and poller_group IN({}) '
    .format(str(config['distributed_poller_group'])) if 'distributed_poller_group' in config else '')

# Add last_polled and last_polled_timetaken so we can sort by the time the last poll started, with the goal
# of having each device complete a poll within the given time range.
dev_query = (   'SELECT device_id,                                  '
                'DATE_ADD(                                          '
                '  DATE_SUB(                                        '
                '    last_polled,                                   '
                '    INTERVAL last_polled_timetaken SECOND          '
                '  ),                                               '
                '  INTERVAL {0} SECOND) AS next_poll                '
                'FROM devices WHERE                                 '
                'disabled = 0                                       '
                'AND IS_FREE_LOCK(CONCAT("polling.", device_id))    '
                'AND IS_FREE_LOCK(CONCAT("queued.", device_id))     '
                '{1}                                                '
                'ORDER BY next_poll asc                             ').format(frequency, poller_group)

dont_retry = {}
threads = 0

while True:
    cur_threads = threading.active_count()
    if cur_threads != threads:
        threads = cur_threads
        print 'INFO: {} threads currently active'.format(threads)

    dont_retry = dict((dev, time) for dev, time in dont_retry.iteritems() if time > datetime.now())

    while threading.active_count() >= amount_of_workers:
        time.sleep(.5)

    cursor.execute(dev_query)
    devices = cursor.fetchall()
    for device_id, next_poll in devices:
        # add queue lock, so we lock the next device against any other pollers
        # if this fails, the device is locked by another poller already
        if not getLock('queued.{}'.format(device_id)):
            continue
        if not lockFree('polling.{}'.format(device_id)):
            releaseLock('queued.{}'.format(device_id))
            continue
        if device_id in dont_retry:
            releaseLock('queued.{}'.format(device_id))
            continue

        if next_poll > datetime.now():
            next_retry_device = min(dont_retry, key=dont_retry.get)
            next_retry_time = dont_retry[next_retry_device]
            if next_retry_time < next_poll:
                device_id = next_retry_device
                if not getLock('queued.{}'.format(device_id)):
                    continue
                print 'INFO: Sleeping until {} before retrying failed device {}'.format(next_retry_time, device_id)
                sleep_until(next_retry_time)
            else:
                print 'INFO: Sleeping until {} before polling {}'.format(next_poll, device_id)
                sleep_until(next_poll)

        print 'INFO: Starting poll of device {}'.format(device_id)
        dont_retry[device_id] = datetime.now() + timedelta(seconds=down_retry)
        t = threading.Thread(target=poll_worker, args=[device_id])
        t.start()

        # If we made it this far, break out of the loop and query again.
        break

try:
    print_queue.join()
except (KeyboardInterrupt, SystemExit):
    raise
