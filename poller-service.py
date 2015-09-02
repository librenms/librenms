#! /usr/bin/env python
"""
 poller-service A service to wrap SNMP polling.  It will poll up to $threads devices at a time, and will not re-poll
                devices that have been polled within the last $poll_frequency seconds. It will prioritize devices based
                on the last time polled. If resources are sufficient, this service should poll every device every
                $poll_frequency seconds, but should gracefully degrade if resources are inefficient, polling devices as
                frequently as possible. This service is based on Job Snijders' poller-wrapper.py.

 Author:        Clint Armstrong <clint@clintarmstrong.net>
 Date:          July 2015

 License:       BSD 2-Clause

Copyright (c) 2015, Clint Armstrong
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
"""

import json
import os
import subprocess
import sys
import threading
import time
import MySQLdb
import logging
import logging.handlers
from datetime import datetime, timedelta
from collections import namedtuple

log = logging.getLogger('poller-service')
log.setLevel(logging.DEBUG)

formatter = logging.Formatter('poller-service: %(message)s')
handler = logging.handlers.SysLogHandler(address='/dev/log')
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
    return proc.communicate()[0].decode()

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
    loglevel = int(config['poller_service_loglevel'])
except KeyError:
    loglevel = 20
except ValueError:
    loglevel = logging.getLevelName(config['poller_service_loglevel'])

try:
    log.setLevel(loglevel)
except ValueError:
    log.warning('ERROR: {0} is not a valid log level. If using python 3.4.0-3.4.1 you must specify loglevel by number'.format(str(loglevel)))
    log.setLevel(20)

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
        amount_of_workers = 16
except KeyError:
    amount_of_workers = 16

try:
    poll_frequency = int(config['poller_service_poll_frequency'])
    if poll_frequency == 0:
        poll_frequency = 300
except KeyError:
    poll_frequency = 300

try:
    discover_frequency = int(config['poller_service_discover_frequency'])
    if discover_frequency == 0:
        discover_frequency = 21600
except KeyError:
    discover_frequency = 21600

try:
    down_retry = int(config['poller_service_down_retry'])
    if down_retry == 0:
        down_retry = 60
except KeyError:
    down_retry = 60

def connectDB():
    try:
        if db_port == 0:
            db_inst = MySQLdb.connect(host=db_server, user=db_username, passwd=db_password, db=db_dbname)
        else:
            db_inst = MySQLdb.connect(host=db_server, port=db_port, user=db_username, passwd=db_password, db=db_dbname)
        db_inst.autocommit(True)
        cursor_inst = db_inst.cursor()
        ret = namedtuple('db_connection', ['db', 'cursor'])
        ret.db = db_inst
        ret.cursor = cursor_inst
        return ret
    except:
        log.critical("ERROR: Could not connect to MySQL database!")
        sys.exit(2)

db_connection = connectDB()
db = db_connection.db
cursor = db_connection.cursor

def poll_worker(device_id, action):
    try:
        start_time = time.time()
        path = poller_path
        if action == 'discovery':
            path = discover_path
        command = "/usr/bin/env php %s -h %s >> /dev/null 2>&1" % (path, device_id)
        subprocess.check_call(command, shell=True)
        elapsed_time = int(time.time() - start_time)
        if elapsed_time < 300:
            log.debug("DEBUG: worker finished %s of device %s in %s seconds" % (action, device_id, elapsed_time))
        else:
            log.warning("WARNING: worker finished %s of device %s in %s seconds" % (action, device_id, elapsed_time))
    except (KeyboardInterrupt, SystemExit):
        raise
    except:
        pass
    finally:
        releaseThreadLock(device_id, action)
        


def lockFree(lock, cursor=cursor):
    query = "SELECT IS_FREE_LOCK('{0}')".format(lock)
    cursor.execute(query)
    return cursor.fetchall()[0][0] == 1


def getLock(lock, cursor=cursor):
    query = "SELECT GET_LOCK('{0}', 0)".format(lock)
    cursor.execute(query)
    return cursor.fetchall()[0][0] == 1


thread_cursors = []
for i in range(0, amount_of_workers):
    thread_cursors.append(namedtuple('Cursor{0}'.format(i), ['in_use', 'cursor', 'db']))
    thread_cursors[i].in_use = False
    thread_db_connection = connectDB()
    thread_cursors[i].cursor = thread_db_connection.cursor
    thread_cursors[i].db = thread_db_connection.db


def getThreadQueueLock(device_id):
    global thread_cursors
    # This is how threads are limited, by the numver of cursors available
    while True:
        for thread_cursor in thread_cursors:
            if not thread_cursor.in_use:
                thread_cursor.in_use = 'queue.{0}'.format(device_id)
                if getLock('queue.{0}'.format(device_id), thread_cursor.cursor):
                    return True
                else:
                    thread_cursor.in_use = False
        time.sleep(.5)


def getThreadActionLock(device_id, action):
    global thread_cursors
    # This is how threads are limited, by the numver of cursors available
    for thread_cursor in thread_cursors:
        if thread_cursor.in_use == 'queue.{0}'.format(device_id):
            thread_cursor.in_use = '{0}.{1}'.format(action, device_id)
            if getLock('{0}.{1}'.format(action, device_id), thread_cursor.cursor):
                return True
            else:
                thread_cursor.in_use = False
    return False


def releaseLock(lock, cursor=cursor):
    query = "SELECT RELEASE_LOCK('{0}')".format(lock)
    cursor.execute(query)
    return cursor.fetchall()[0][0] == 1


def releaseThreadLock(device_id, action):
    global thread_cursors
    for thread_cursor in thread_cursors:
        if thread_cursor.in_use == '{0}.{1}'.format(action, device_id):
            thread_cursor.in_use = False
            return releaseLock('{0}.{1}'.format(action, device_id), thread_cursor.cursor)
    return False


def sleep_until(timestamp):
    now = datetime.now()
    if timestamp > now:
        sleeptime = (timestamp - now).seconds
    else:
        sleeptime = 0
    time.sleep(sleeptime)

poller_group = ('and poller_group IN({0}) '
                .format(str(config['distributed_poller_group'])) if 'distributed_poller_group' in config else '')

# Add last_polled and last_polled_timetaken so we can sort by the time the last poll started, with the goal
# of having each device complete a poll within the given time range.
dev_query = ('SELECT device_id, status,                                          '
             'CAST(                                                              '
             '  DATE_ADD(                                                        '
             '    DATE_SUB(                                                      '
             '      last_polled,                                                 '
             '      INTERVAL last_polled_timetaken SECOND                        '
             '    ),                                                             '
             '    INTERVAL {0} SECOND)                                           '
             '  AS DATETIME(0)                                                   '
             ') AS next_poll,                                                    '
             'CAST(                                                              '
             '  DATE_ADD(                                                        '
             '    DATE_SUB(                                                      '
             '      last_discovered,                                             '
             '      INTERVAL last_discovered_timetaken SECOND                    '
             '    ),                                                             '
             '    INTERVAL {1} SECOND)                                           '
             '  AS DATETIME(0)                                                   '
             ') as next_discovery                                                '
             'FROM devices WHERE                                                 '
             'disabled = 0                                                       '
             'AND IS_FREE_LOCK(CONCAT("poll.", device_id))                       '
             'AND IS_FREE_LOCK(CONCAT("discovery.", device_id))                       '
             'AND IS_FREE_LOCK(CONCAT("queue.", device_id))                       '
             'AND ( last_poll_attempted < DATE_SUB(NOW(), INTERVAL {2} SECOND )  '
             '  OR last_poll_attempted IS NULL )                                 '
             '{3}                                                                '
             'ORDER BY next_poll asc                                             '
             'LIMIT 5                                                            ').format(poll_frequency,
                                                                                           discover_frequency,
                                                                                           down_retry,
                                                                                           poller_group)

threads = 0
next_update = datetime.now() + timedelta(minutes=1)
devices_scanned = 0

while True:
    cur_threads = threading.active_count()
    if cur_threads != threads:
        threads = cur_threads
        log.debug('DEBUG: {0} threads currently active'.format(threads))

    if next_update < datetime.now():
        seconds_taken = (datetime.now() - (next_update - timedelta(minutes=1))).seconds
        update_query = ('INSERT INTO pollers(poller_name,     '
                        '                    last_polled,     '
                        '                    devices,         '
                        '                    time_taken)      '
                        '  values("{0}", NOW(), "{1}", "{2}") '
                        'ON DUPLICATE KEY UPDATE              '
                        '  last_polled=values(last_polled),   '
                        '  devices=values(devices),           '
                        '  time_taken=values(time_taken)      ').format(config['distributed_poller_name'].strip(),
                                                                        devices_scanned,
                                                                        seconds_taken)
        try:
            cursor.execute(update_query)
        except:
            log.critical('ERROR: MySQL query error. Is your schema up to date?')
            sys.exit(2)
        cursor.fetchall()
        log.info('INFO: {0} devices scanned in the last minute'.format(devices_scanned))
        devices_scanned = 0
        next_update = datetime.now() + timedelta(minutes=1)

    try:
        cursor.execute(dev_query)
    except:
        log.critical('ERROR: MySQL query error. Is your schema up to date?')
        sys.exit(2)

    devices = cursor.fetchall()
    for device_id, status, next_poll, next_discovery in devices:
        if not getThreadQueueLock(device_id):
            continue

        if next_poll and next_poll > datetime.now():
            log.debug('DEBUG: Sleeping until {0} before polling {1}'.format(next_poll, device_id))
            sleep_until(next_poll)

        action = 'poll'
        if (not next_discovery or next_discovery < datetime.now()) and status == 1:
            action = 'discovery'

        log.debug('DEBUG: Starting {0} of device {1}'.format(action, device_id))
        devices_scanned += 1

        cursor.execute('UPDATE devices SET last_poll_attempted = NOW() WHERE device_id = {0}'.format(device_id))
        cursor.fetchall()

        if not getThreadActionLock(device_id, action):
            continue

        t = threading.Thread(target=poll_worker, args=[device_id, action])
        t.start()

        # If we made it this far, break out of the loop and query again.
        break

    # This point is only reached if the query is empty, so sleep half a second before querying again.
    time.sleep(.5)

    # Make sure we're not holding any device queue locks in this connection before querying again
    # by locking a different string.
    getLock('unlock.{0}'.format(config['distributed_poller_name']))
