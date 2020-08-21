#! /usr/bin/env python3
"""
 poller-wrapper A small tool which wraps around the poller and tries to
                guide the polling process with a more modern approach with a
                Queue and workers

 Authors:       Job Snijders <job.snijders@atrato.com>
                Orsiris de Jong <contact@netpower.fr>
 Date:          Oct 2019

 Usage:         This program accepts one command line argument: the number of threads
                that should run simultaneously. If no argument is given it will assume
                a default of 16 threads.

 Ubuntu Linux:  apt-get install python-mysqldb
 FreeBSD:       cd /usr/ports/*/py-MySQLdb && make install clean
 RHEL 7:        yum install MySQL-python
 RHEL 8:        dnf install mariadb-connector-c-devel gcc && python -m pip install mysqlclient

 Tested on:     Python 3.6.8 / PHP 7.2.11 / CentOS 8.0

 License:       To the extent possible under law, Job Snijders has waived all
                copyright and related or neighboring rights to this script.
                This script has been put into the Public Domain. This work is
                published from: The Netherlands.
"""

import LibreNMS.library as LNMS

try:

    import json
    import os
    import queue
    import subprocess
    import sys
    import threading
    import time
    from optparse import OptionParser

except ImportError as exc:
    print('ERROR: missing one or more of the following python modules:')
    print('threading, queue, sys, subprocess, time, os, json')
    print('ERROR: %s' % exc)
    sys.exit(2)


APP_NAME = "poller_wrapper"
LOG_FILE = "logs/" + APP_NAME + ".log"
_DEBUG = False
distpoll = False
real_duration = 0
polled_devices = 0

"""
 Threading helper functions
"""
# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC0
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


def get_time_tag(step):
    ts = int(time.time())
    return ts - ts % step
#EOC0

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
                memc_touch(master_tag, 10)
                nodes = memc.get(nodes_tag)
                if nodes is None and not memc_alive():
                    print("WARNING: Lost Memcached. Taking over all devices. Nodes will quit shortly.")
                    distpoll = False
                    nodes = nodeso
                if nodes is not nodeso:
                    print("INFO: %s Node(s) Total" % (nodes))
                    nodeso = nodes
            else:
                memc_touch(nodes_tag, 10)
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
        if elapsed_time < step:
            print("INFO: worker %s finished device %s in %s seconds" % (worker_id, device_id, elapsed_time))
        else:
            print("WARNING: worker %s finished device %s in %s seconds" % (worker_id, device_id, elapsed_time))
        print_queue.task_done()


"""
    This class will fork off single instances of the poller.php process, record
    how long it takes, and push the resulting reports to the printer queue
"""
def poll_worker():
    while True:
        device_id = poll_queue.get()
        # (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC5
        if not distpoll or memc.get('poller.device.%s.%s' % (device_id, time_tag)) is None:
            if distpoll:
                result = memc.add('poller.device.%s.%s' % (device_id, time_tag), config['distributed_poller_name'],
                                  step)
                if not result:
                    print("This device (%s) appears to be being polled by another poller" % (device_id))
                    poll_queue.task_done()
                    continue
                if not memc_alive() and IsNode:
                    print("Lost Memcached, Not polling Device %s as Node. Master will poll it." % device_id)
                    poll_queue.task_done()
                    continue
            # EOC5
            try:
                start_time = time.time()

                output = "-d >> %s/poll_device_%s.log" % (log_dir, device_id) if debug else ">> /dev/null"
                command = "/usr/bin/env php %s -h %s %s 2>&1" % (poller_path, device_id, output)
                # TODO: replace with command_runner
                subprocess.check_call(command, shell=True)

                elapsed_time = int(time.time() - start_time)
                print_queue.put([threading.current_thread().name, device_id, elapsed_time])
            except (KeyboardInterrupt, SystemExit):
                raise
            except:
                pass
        poll_queue.task_done()


if __name__ == '__main__':
    logger = LNMS.logger_get_logger(LOG_FILE, debug=_DEBUG)

    install_dir = os.path.dirname(os.path.realpath(__file__))
    LNMS.check_for_file(install_dir + '/.env')
    config = json.loads(LNMS.get_config_data(install_dir))

    poller_path = config['install_dir'] + '/poller.php'
    log_dir = config['log_dir']

    if 'rrd' in config and 'step' in config['rrd']:
        step = config['rrd']['step']
    else:
        step = 300


    # (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC1
    if 'distributed_poller_group' in config:
        poller_group = str(config['distributed_poller_group'])
    else:
        poller_group = False

    if ('distributed_poller' in config and
        'distributed_poller_memcached_host' in config and
        'distributed_poller_memcached_port' in config and
        config['distributed_poller']):

        time_tag = str(get_time_tag(step))
        master_tag = "poller.master." + time_tag
        nodes_tag = "poller.nodes." + time_tag

        try:
            import memcache
            import uuid

            memc = memcache.Client([config['distributed_poller_memcached_host'] + ':' +
                                    str(config['distributed_poller_memcached_port'])])
            if str(memc.get(master_tag)) == config['distributed_poller_name']:
                print("This system is already joined as the poller master.")
                sys.exit(2)
            if memc_alive():
                if memc.get(master_tag) is None:
                    print("Registered as Master")
                    memc.set(master_tag, config['distributed_poller_name'], 10)
                    memc.set(nodes_tag, 0, step)
                    IsNode = False
                else:
                    print("Registered as Node joining Master %s" % memc.get(master_tag))
                    IsNode = True
                    memc.incr(nodes_tag)
                distpoll = True
            else:
                print("Could not connect to memcached, disabling distributed poller.")
                distpoll = False
                IsNode = False
        except SystemExit:
            raise
        except ImportError:
            print("ERROR: missing memcache python module:")
            print("On deb systems: apt-get install python3-memcache")
            print("On other systems: pip3 install python-memcached")
            print("Disabling distributed poller.")
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
    usage = "usage: %prog [options] <workers> (Default: 16 (Do not set too high)"
    description = "Spawn multiple poller.php processes in parallel."
    parser = OptionParser(usage=usage, description=description)
    parser.add_option('-d', '--debug', action='store_true', default=False,
                      help="Enable debug output. WARNING: Leaving this enabled will consume a lot of disk space.")
    (options, args) = parser.parse_args()

    debug = options.debug
    try:
        amount_of_workers = int(args[0])
    except (IndexError, ValueError):
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
        query = 'select device_id from devices where poller_group IN(' + poller_group + \
                ') and disabled = 0 order by last_polled_timetaken desc'
    else:
        query = 'select device_id from devices where disabled = 0 order by last_polled_timetaken desc'
    # EOC2

    db = LNMS.db_open(config['db_socket'], config['db_host'], config['db_port'], config['db_user'], config['db_pass'], config['db_name'])
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
        maxlocks = devices[0][0] or 0
        minlocks = devices[0][1] or 0
    # EOC3
    db.close()

    poll_queue = queue.Queue()
    print_queue = queue.Queue()

    print(
        "INFO: starting the poller at %s with %s threads, slowest devices first" % (time.strftime("%Y-%m-%d %H:%M:%S"),
                                                                                    amount_of_workers))

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

    print("INFO: poller-wrapper polled %s devices in %s seconds with %s workers" % (
        polled_devices, total_time, amount_of_workers))

    # (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC6
    if distpoll or memc_alive():
        master = memc.get(master_tag)
        if master == config['distributed_poller_name'] and not IsNode:
            print("Wait for all poller-nodes to finish")
            nodes = memc.get(nodes_tag)
            while nodes is not None and nodes > 0:
                try:
                    time.sleep(1)
                    nodes = memc.get(nodes_tag)
                except:
                    pass
            print("Clearing Locks for %s" % time_tag)
            x = minlocks
            while x <= maxlocks:
                res = memc.delete('poller.device.%s.%s' % (x, time_tag))
                x += 1
            print("%s Locks Cleared" % x)
            print("Clearing Nodes")
            memc.delete(master_tag)
            memc.delete(nodes_tag)
        else:
            memc.decr(nodes_tag)
        print("Finished %.3fs after interval start." % (time.time() - int(time_tag)))
    # EOC6

    show_stopper = False

    db = LNMS.db_open(config['db_socket'], config['db_host'], config['db_port'], config['db_user'], config['db_pass'], config['db_name'])
    cursor = db.cursor()
    query = "update pollers set last_polled=NOW(), devices='%d', time_taken='%d' where poller_name='%s'" % (
        polled_devices,
        total_time,
        config['distributed_poller_name'])
    response = cursor.execute(query)
    if response == 1:
        db.commit()
    else:
        query = "insert into pollers set poller_name='%s', last_polled=NOW(), devices='%d', time_taken='%d'" % (
            config['distributed_poller_name'], polled_devices, total_time)
        cursor.execute(query)
        db.commit()
    db.close()

    if total_time > step:
        print(
            "WARNING: the process took more than %s seconds to finish, you need faster hardware or more threads" % step)
        print("INFO: in sequential style polling the elapsed time would have been: %s seconds" % real_duration)
        for device in per_device_duration:
            if per_device_duration[device] > step:
                print("WARNING: device %s is taking too long: %s seconds" % (device, per_device_duration[device]))
                show_stopper = True
        if show_stopper:
            print(
                "ERROR: Some devices are taking more than %s seconds, the script cannot recommend you what to do." % step)
        else:
            recommend = int(total_time / step * amount_of_workers + 1)
            print(
                "WARNING: Consider setting a minimum of %d threads. (This does not constitute professional advice!)" % recommend)

        sys.exit(2)
