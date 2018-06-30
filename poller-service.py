#! /usr/bin/env python2
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


class DB:
    conn = None

    def __init__(self):
        self.in_use = threading.Lock()
        self.connect()

    def connect(self):
        self.in_use.acquire(True)
        while True:
            try:
                self.conn.close()
            except:
                pass

            try:
                if db_port == 0:
                    self.conn = MySQLdb.connect(host=db_server, user=db_username, passwd=db_password, db=db_dbname)
                else:
                    self.conn = MySQLdb.connect(host=db_server, port=db_port, user=db_username, passwd=db_password, db=db_dbname)
                break
            except (AttributeError, MySQLdb.OperationalError):
                log.warning('WARNING: MySQL Error, reconnecting.')
                time.sleep(.5)

        self.conn.autocommit(True)
        self.conn.ping(True)
        self.in_use.release()

    def query(self, sql):
        self.in_use.acquire(True)
        while True:
            try:
                cursor = self.conn.cursor()
                cursor.execute(sql)
                ret = cursor.fetchall()
                cursor.close()
                self.in_use.release()
                return ret
            except (AttributeError, MySQLdb.OperationalError):
                log.warning('WARNING: MySQL Operational Error during query, reconnecting.')
                self.in_use.release()
                self.connect()
            except (AttributeError, MySQLdb.ProgrammingError):
                log.warning('WARNING: MySQL Programming Error during query, attempting query again.')
                cursor.close()


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
db_port = int(config['db_port'])

if config['db_host'][:5].lower() == 'unix:':
    db_server = config['db_host']
    db_port = 0
elif config['db_socket']:
    db_server = config['db_socket']
    db_port = 0
else:
    db_server = config['db_host']

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

try:
    retry_query = int(config['poller_service_retry_query'])
    if retry_query == 0:
        retry_query = 1
except KeyError:
    retry_query = 1

try:
    single_connection = bool(config['poller_service_single_connection'])
except KeyError:
    single_connection = False

db = DB()


def lockFree(lock, db=db):
    query = "SELECT IS_FREE_LOCK('{0}')".format(lock)
    return db.query(query)[0][0] == 1


def getLock(lock, db=db):
    query = "SELECT GET_LOCK('{0}', 0)".format(lock)
    return db.query(query)[0][0] == 1


def releaseLock(lock, db=db):
    query = "SELECT RELEASE_LOCK('{0}')".format(lock)
    cursor = db.query(query)
    return db.query(query)[0][0] == 1


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
             '  AS DATETIME                                                      '
             ') AS next_poll,                                                    '
             'CAST(                                                              '
             '  DATE_ADD(                                                        '
             '    DATE_SUB(                                                      '
             '      last_discovered,                                             '
             '      INTERVAL last_discovered_timetaken SECOND                    '
             '    ),                                                             '
             '    INTERVAL {1} SECOND)                                           '
             '  AS DATETIME                                                      '
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
             'LIMIT 1                                                            ').format(poll_frequency,
                                                                                           discover_frequency,
                                                                                           down_retry,
                                                                                           poller_group)

next_update = datetime.now() + timedelta(minutes=1)
devices_scanned = 0

dont_query_until = datetime.fromtimestamp(0)

def poll_worker():
    global dev_query
    global devices_scanned
    global dont_query_until
    global single_connection
    thread_id = threading.current_thread().name

    if single_connection:
        global db
    else:
        db = DB()

    while True:
        if datetime.now() < dont_query_until:
            time.sleep(1)
            continue

        dev_row = db.query(dev_query)
        if len(dev_row) < 1:
            dont_query_until = datetime.now() + timedelta(seconds=retry_query)
            time.sleep(1)
            continue
            
        device_id, status, next_poll, next_discovery  = dev_row[0]

        if not getLock('queue.{0}'.format(device_id), db):
            releaseLock('queue.{0}'.format(device_id), db)
            continue

        if next_poll and next_poll > datetime.now():
            log.debug('DEBUG: Thread {0} Sleeping until {1} before polling {2}'.format(thread_id, next_poll, device_id))
            sleep_until(next_poll)

        action = 'poll'
        if (not next_discovery or next_discovery < datetime.now()) and status == 1:
            action = 'discovery'

        log.debug('DEBUG: Thread {0} Starting {1} of device {2}'.format(thread_id, action, device_id))
        devices_scanned += 1

        db.query('UPDATE devices SET last_poll_attempted = NOW() WHERE device_id = {0}'.format(device_id))

        if not getLock('{0}.{1}'.format(action, device_id), db):
            releaseLock('{0}.{1}'.format(action, device_id), db)
            releaseLock('queue.{0}'.format(device_id), db)
            continue

        releaseLock('queue.{0}'.format(device_id), db)
        try:
            start_time = time.time()
            path = poller_path
            if action == 'discovery':
                path = discover_path
            command = "/usr/bin/env php %s -h %s >> /dev/null 2>&1" % (path, device_id)
            subprocess.check_call(command, shell=True)
            elapsed_time = int(time.time() - start_time)
            if elapsed_time < 300:
                log.debug("DEBUG: Thread {0} finished {1} of device {2} in {3} seconds".format(thread_id, action, device_id, elapsed_time))
            else:
                log.warning("WARNING: Thread {0} finished {1} of device {2} in {3} seconds".format(thread_id, action, device_id, elapsed_time))
        except (KeyboardInterrupt, SystemExit):
            raise
        except:
            pass
        finally:
            releaseLock('{0}.{1}'.format(action, device_id), db)
        

for i in range(0, amount_of_workers):
    t = threading.Thread(target=poll_worker)
    t.name = i
    t.daemon = True
    t.start()


while True:
    sleep_until(next_update)

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
        db.query(update_query)
    except:
        log.critical('ERROR: MySQL query error. Is your schema up to date?')
        sys.exit(2)
    log.info('INFO: {0} devices scanned in the last minute'.format(devices_scanned))
    devices_scanned = 0
    next_update = datetime.now() + timedelta(minutes=1)
