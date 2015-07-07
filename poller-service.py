#! /usr/bin/env python
"""
 poller-service A service to wrap SNMP polling.  It will poll up to $threads devices at a time, and will not re-poll
                devices that have been polled within the last $poll_frequency seconds. It will prioritize devices based on
                the last time polled. If resources are sufficient, this service should poll every device every
                $poll_frequency seconds, but should gracefully degrade if resources are inefficient, polling devices as
                frequently as possible. This service is based on poller-wrapper.py.

 Author:        Clint Armstrong <clint@clintarmstrong.net>
 Date:          July 2015

 Usage:         poller-service [threads] [poll_frequency]
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
import logging
import logging.handlers
from datetime import datetime, timedelta

log = logging.getLogger('poller-service')
log.setLevel(logging.DEBUG)

formatter = logging.Formatter('poller-service: %(message)s')
handler = logging.handlers.SysLogHandler(address= '/dev/log')
handler.setFormatter(formatter)
log.addHandler(handler)

install_dir = os.path.dirname(os.path.realpath(__file__))
config_file = install_dir + '/config.php'

log.info('INFO: Starting poller-service')

def get_config_data():
    config_cmd = ['/usr/bin/env', 'php', '%s/config_to_json.php' % install_dir]
    try:
        proc = subprocess.Popen(config_cmd, stdout=subprocess.PIPE, stdin=subprocess.PIPE)
    except:
        log.critical("ERROR: Could not execute: %s" % config_cmd)
        sys.exit(2)
    return proc.communicate()[0]

try:
    with open(config_file) as f:
        pass
except IOError as e:
    log.critical("ERROR: Oh dear... %s does not seem readable" % config_file)
    sys.exit(2)

try:
    config = json.loads(get_config_data())
except:
    log.critical("ERROR: Could not load or parse configuration, are PATHs correct?")
    sys.exit(2)

try:
    loglevel = logging.getLevelName(config['poller_service_loglevel'].upper())
except KeyError:
    loglevel = logging.getLevelName('INFO')
if not isinstance(loglevel, int):
    log.warning('ERROR: {} is not a valid log level'.format(str(loglevel)))
    loglevel = logging.getLevelName('INFO')
log.setLevel(loglevel)

poller_path = config['install_dir'] + '/poller.php'
discover_path = config['install_dir'] + '/discovery.php'
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


try:
    amount_of_workers = int(config['poller_service_workers'])
    if amount_of_workers == 0:
        log.critical("ERROR: 0 threads is not a valid value")
        sys.exit(2)
except KeyError:
    amount_of_workers = 16

try:
    poll_frequency = int(config['poller_service_poll_frequency'])
    if poll_frequency == 0:
        log.critical("ERROR: 0 seconds is not a valid value")
        sys.exit(2)
except KeyError:
    poll_frequency = 300

try:
    discover_frequency = int(config['poller_service_discover_frequency'])
    if poll_frequency == 0:
        log.critical("ERROR: 0 seconds is not a valid value")
        sys.exit(2)
except KeyError:
    poll_frequency = 21600

try:
    down_retry = int(config['poller_service_down_retry'])
    if down_retry == 0:
        log.critical("ERROR: 0 seconds is not a valid value")
        sys.exit(2)
except KeyError:
    down_retry = 15

try:
    if db_port == 0:
        db = MySQLdb.connect(host=db_server, user=db_username, passwd=db_password, db=db_dbname)
    else:
        db = MySQLdb.connect(host=db_server, port=db_port, user=db_username, passwd=db_password, db=db_dbname)
    db.autocommit(True)
    cursor = db.cursor()
except:
    log.critical("ERROR: Could not connect to MySQL database!")
    sys.exit(2)


def poll_worker(device_id, action):
    try:
        start_time = time.time()
        path = poller_path
        if action = 'discovery'
            path = discovery_path
        command = "/usr/bin/env php %s -h %s >> /dev/null 2>&1" % (path, device_id)
        subprocess.check_call(command, shell=True)
        elapsed_time = int(time.time() - start_time)
        if elapsed_time < 300:
            log.debug("DEBUG: worker finished device %s in %s seconds" % (device_id, elapsed_time))
        else:
            log.warning("WARNING: worker finished device %s in %s seconds" % (device_id, elapsed_time))
    except (KeyboardInterrupt, SystemExit):
        raise
    except:
        pass


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
                'DATE_ADD(                                          '
                '  DATE_SUB(                                        '
                '    last_discovered,                               '
                '    INTERVAL last_discovered_timetaken SECOND      '
                '  ),                                               '
                '  INTERVAL {1} SECOND) AS next_discovery           '
                'FROM devices WHERE                                 '
                'disabled = 0                                       '
                'AND IS_FREE_LOCK(CONCAT("polling.", device_id))    '
                'AND IS_FREE_LOCK(CONCAT("queued.", device_id))     '
                '{2}                                                '
                'ORDER BY next_poll asc                             ').format( poll_frequency,
                                                                               discover_frequency,
                                                                               poller_group        )

dont_retry = {}
threads = 0
next_update = datetime.now() + timedelta(minutes=5)
devices_scanned = 0

while True:
    cur_threads = threading.active_count()
    if cur_threads != threads:
        threads = cur_threads
        log.debug('DEBUG: {} threads currently active'.format(threads))

    if next_update < datetime.now():
        log.info('INFO: {} devices scanned in the last 5 minutes'.format(devices_scanned))
        devices_scanned = 0
        next_update = datetime.now() + timedelta(minutes=5)

    dont_retry = dict((dev, time) for dev, time in dont_retry.iteritems() if time > datetime.now())

    while threading.active_count() >= amount_of_workers:
        time.sleep(.5)

    cursor.execute(dev_query)
    devices = cursor.fetchall()
    retry_devs_found = {}
    for device_id, next_poll, next_discovery in devices:
        # add queue lock, so we lock the next device against any other pollers
        # if this fails, the device is locked by another poller already
        if not getLock('queued.{}'.format(device_id)):
            continue
        if not lockFree('polling.{}'.format(device_id)):
            releaseLock('queued.{}'.format(device_id))
            continue
        if device_id in dont_retry:
            retry_devs_found[device_id] = dont_retry[device_id]
            releaseLock('queued.{}'.format(device_id))
            continue

        if next_poll > datetime.now():
            poll_action = 'polling'
            sleep_until_time = next_poll
            try:
                next_retry_device = min(retry_devs_found, key=retry_devs_found.get)
                next_retry_time = retry_devs_found[next_retry_device]
                if next_retry_time < next_poll:
                    device_id = next_retry_device
                    if not getLock('queued.{}'.format(device_id)):
                        continue
                    poll_action = 'retrying failed device'
                    sleep_until_time = next_retry_time
            except ValueError:
                pass

            log.debug('DEBUG: Sleeping until {0} before {1} {2}'.format(next_poll, poll_action, device_id))
            sleep_until(sleep_until_time)

        action = 'poll'
        if not next_discovery or next_discovery < datetime.now():
            action = 'discovery'

        log.debug('DEBUG: Starting {} of device {}'.format(action, device_id))
        devices_scanned += 1
        dont_retry[device_id] = datetime.now() + timedelta(seconds=down_retry)
        t = threading.Thread(target=poll_worker, args=[device_id, action])
        t.start()

        # If we made it this far, break out of the loop and query again.
        break

try:
    print_queue.join()
except (KeyboardInterrupt, SystemExit):
    raise
