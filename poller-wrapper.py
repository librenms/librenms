#! /usr/bin/env python
"""
 poller-wrapper A small tool which wraps around the Observium poller
                and tries to guide the polling process with a more modern
                approach with a Queue and workers

 Author:        Job Snijders <job.snijders@atrato.com>
 Date:          Jan 2013

 Usage:         This program accepts one command line argument: the number of threads
                that should run simultaneously. If no argument is given it will assume
                a default of 16 threads.

                In /etc/cron.d/observium replace this (or the equivalent) poller entry:
                */5 *     * * *   root    /opt/observium/poller.php -h all >> /dev/null 2>&1
                with something like this:
                */5 * * * * root python /opt/observium/poller-wrapper.py 16 >> /dev/null 2>&1

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
    import threading, Queue, sys, subprocess, time, os, json
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
    Parse Arguments
    Attempt to use argparse module.  Probably want to use this moving forward
    especially as more features want to be added to this wrapper.
    and
    Take the amount of threads we want to run in parallel from the commandline
    if None are given or the argument was garbage, fall back to default of 16
"""
try:
    import argparse
    parser = argparse.ArgumentParser(description='Poller Wrapper for Observium')
    parser.add_argument('workers', nargs='?', type=int, default=16, help='Number of workers to spawn.')
    parser.add_argument('--host', help='Poll hostname wildcard.')
    args = parser.parse_args()
    amount_of_workers = int(args.workers)
except ImportError:
    print "WARNING: missing the argparse python module:"
    print "On ubuntu: apt-get install libpython2.7-stdlib"
    print "On debian: apt-get install python-argparse"
    print "Continuing with basic argument support."
    try:
        amount_of_workers = int(sys.argv[1])
    except:
        amount_of_workers = 16

if amount_of_workers == 0:
    print "ERROR: 0 threads is not a valid value"
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
    with open(config_file) as f: pass
except IOError as e:
    print "ERROR: Oh dear... %s does not seem readable" % config_file
    sys.exit(2)

try:
    config = json.loads(get_config_data())
except:
    print "ERROR: Could not load or parse observium configuration, are PATHs correct?"
    sys.exit(2)

poller_path  = config['install_dir'] + '/poller.php'
alerter_path = config['install_dir'] + '/alerter.php'
db_username  = config['db_user']
db_password  = config['db_pass']
db_server    = config['db_host']
db_dbname    = config['db_name']
alerting     = config['poller-wrapper']['alerter']

s_time = time.time()
real_duration = 0
per_device_duration = {}

devices_list = []

try:
    db = MySQLdb.connect (host=db_server, user=db_username , passwd=db_password, db=db_dbname)
    cursor = db.cursor()
except:
    print "ERROR: Could not connect to MySQL database!"
    sys.exit(2)


"""
    This query specifically orders the results depending on the last_polled_timetaken variable
    Because this way, we put the devices likely to be slow, in the top of the queue
    thus greatening our chances of completing _all_ the work in exactly the time it takes to
    poll the slowest device! cool stuff he
    Additionally, if a hostname wildcard is passed, add it to the where clause.  This is
    important in cases where you have pollers distributed geographically and want to limit
    pollers to polling hosts matching their geographic naming scheme.
"""

query = """SELECT   device_id
           FROM     devices
           WHERE    disabled != '1'"""
order =  " ORDER BY last_polled_timetaken DESC"

try:
    host_wildcard = args.host.replace('*', '%')
    wc_query = query + " AND hostname LIKE %s " + order
    cursor.execute(wc_query, host_wildcard)
except:
    query = query + order
    cursor.execute(query)

devices = cursor.fetchall()
for row in devices:
    devices_list.append(int(row[0]))
db.close()

"""
    A seperate queue and a single worker for printing information to the screen prevents
    the good old joke:

        Some people, when confronted with a problem, think,
        "I know, I'll use threads," and then two they hav erpoblesms.
"""

def printworker():
    while True:
        worker_id, device_id, elapsed_time = print_queue.get()
        global real_duration
        global per_device_duration
        real_duration += elapsed_time
        per_device_duration[device_id] = elapsed_time
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
        try:
            start_time = time.time()
            command = "/usr/bin/env php %s -h %s >> /dev/null 2>&1" % (poller_path, device_id)
            subprocess.check_call(command, shell=True)
            if alerting == True:
                print "INFO starting alerter.php for %s" % device_id
                command = "/usr/bin/env php %s -h %s >> /dev/null 2>&1" % (alerter_path, device_id)
                print "INFO finished alerter.php for %s" % device_id
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

print "INFO: starting the poller at %s with %s threads, slowest devices first" % (time.time(), amount_of_workers)

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

print "INFO: poller-wrapper polled %s devices in %s seconds with %s workers" % (len(devices_list), total_time, amount_of_workers)

show_stopper = False

if total_time > 300:
    recommend = int(total_time / 300.0 * amount_of_workers + 1)
    print "WARNING: the process took more than 5 minutes to finish, you need faster hardware or more threads"
    print "INFO: in sequential style polling the elapsed time would have been: %s seconds" % real_duration
    for device in per_device_duration:
        if per_device_duration[device] > 300:
            print "WARNING: device %s is taking too long: %s seconds" % (device, per_device_duration[device])
            show_stopper = True
    if show_stopper == True:
        print "ERROR: Some devices are taking more than 300 seconds, the script cannot recommend you what to do."
    if show_stopper == False:
        print "WARNING: Consider setting a minimum of %d threads. (This does not constitute professional advice!)" % recommend
    sys.exit(2)
