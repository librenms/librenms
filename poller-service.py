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
	db_port =0

db_dbname = config['db_name']

# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC1
if 'distributed_poller_group' in config:
    poller_group = str(config['distributed_poller_group'])
else:
    poller_group = False
# EOC1

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
    #except:
    #    pass

print_queue = Queue.Queue()

print "INFO: starting the poller at %s with %s threads" % (time.strftime("%Y-%m-%d %H:%M:%S"),
        amount_of_workers)

p = threading.Thread(target=printworker)
p.setDaemon(True)
p.start()

def lockFree(lock):
    global cursor
    query = "SELECT IS_FREE_LOCK('{}')".format(lock)
    #print 'checking lock: {}'.format(query)
    cursor.execute(query)
    return cursor.fetchall()[0][0] == 1

def getLock(lock):
    global cursor
    query = "SELECT GET_LOCK('{}', 0)".format(lock)
    #print 'getting lock: {}'.format(query)
    cursor.execute(query)
    return cursor.fetchall()[0][0] == 1

def releaseLock(lock):
    global cursor
    query = "SELECT RELEASE_LOCK('{}')".format(lock)
    #print 'releasing lock: {}'.format(query)
    cursor.execute(query)
    return cursor.fetchall()[0][0] == 1

recently_scanned = {}

while True:
    print '{} threads currently active'.format(threading.active_count())
    while threading.active_count() >= amount_of_workers:
        time.sleep(.5)

    #print 'querying for devices'
    query = 'select device_id,last_polled from devices {} disabled = 0 order by last_polled asc'.format(
                        'where poller_group IN({}) and'.format(poller_group) if poller_group else '')
    cursor.execute(query)
    devices = cursor.fetchall()
    dead_retry_in = frequency
    #print "first 5 devices: {}".format(devices[:5])
    for device_id, last_polled in devices:
#        print 'trying device {}'.format(device_id)
#        time.sleep(1)
        if not lockFree('polling.{}'.format(device_id)):
#            print 'polling lock is not free on {} continuing'.format(device_id)
#            time.sleep(1)
            continue
        if not lockFree('queued.{}'.format(device_id)):
#            print 'queued lock is not free on {} continuing'.format(device_id)
#            time.sleep(1)
            continue
        try:
            if ((recently_scanned[device_id] + timedelta(seconds=down_retry)) - datetime.now()).seconds > 1:
                dead_retry_in = ((recently_scanned[device_id] + timedelta(seconds=down_retry)) - datetime.now()).seconds
#                print 'device {} recently scanned already'.format(device_id)
#                time.sleep(1)
                continue
        except KeyError:
            pass

        # add queue lock, so if we sleep, we lock the next device against any other pollers, break
        # if aquiring lock fails
        if not getLock('queued.{}'.format(device_id)):
#            print 'getting queue lock on {} failed'.format(device_id)
#            time.sleep(1)
            break

        if last_polled > datetime.now() - timedelta(seconds=frequency):
            sleeptime = ((last_polled + timedelta(seconds=300)) - datetime.now()).seconds
            if sleeptime > dead_retry_in:
                print 'Sleeping {} seconds before retrying failed device'.format(dead_retry_in, device_id, last_polled)
                time.sleep(dead_retry_in)
                break

            print 'Sleeping {} seconds before polling {}, last polled {}'.format(sleeptime, device_id, last_polled)
            time.sleep(sleeptime)

        print 'Starting poll of device {}, last polled {}'.format(device_id, last_polled)
        recently_scanned[device_id] = datetime.now()
        t = threading.Thread(target=poll_worker, args=[device_id])
        #t.setDaemon(True)
        t.start()
        #print 'thread launched'

        releaseLock('queued.{}'.format(device_id))

        # If we made it this far, break out of the loop and query again.
        break

try:
    print_queue.join()
except (KeyboardInterrupt, SystemExit):
    raise

total_time = int(time.time() - s_time)

print "INFO: poller-wrapper polled %s devices in %s seconds with %s workers" % (polled_devices, total_time, amount_of_workers)

show_stopper = False

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
