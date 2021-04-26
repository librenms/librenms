#! /usr/bin/env python3
"""
 services-wrapper A small tool which wraps around check-services.php and tries to
                guide the services process with a more modern approach with a
                Queue and workers.

 Based on the original version of poller-wrapper.py by Job Snijders

 Author:        Neil Lathwood <neil@librenms.org>
                Orsiris de Jong <contact@netpower.fr>
 Date:          Oct 2019

 Usage:         This program accepts one command line argument: the number of threads
                that should run simultaneously. If no argument is given it will assume
                a default of 1 thread.

 Ubuntu Linux:  apt-get install python-mysqldb
 FreeBSD:       cd /usr/ports/*/py-MySQLdb && make install clean
 RHEL 7:        yum install MySQL-python
 RHEL 8:        dnf install mariadb-connector-c-devel gcc && python -m pip install mysqlclient

 Tested on:     Python 3.6.8 / PHP 7.2.11 / CentOS 8

 License:       This program is free software: you can redistribute it and/or modify it
                under the terms of the GNU General Public License as published by the
                Free Software Foundation, either version 3 of the License, or (at your
                option) any later version.

                This program is distributed in the hope that it will be useful, but
                WITHOUT ANY WARRANTY; without even the implied warranty of
                MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
                Public License for more details.

                You should have received a copy of the GNU General Public License along
                with this program. If not, see https://www.gnu.org/licenses/.

                LICENSE.txt contains a copy of the full GPLv3 licensing conditions.
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
    print("ERROR: missing one or more of the following python modules:")
    print("threading, queue, sys, subprocess, time, os, json")
    print("ERROR: %s" % exc)
    sys.exit(2)


APP_NAME = "services_wrapper"
LOG_FILE = "logs/" + APP_NAME + ".log"
_DEBUG = False
servicedisco = False
real_duration = 0
service_devices = 0

"""
 Threading helper functions
"""
# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC0
def memc_alive():
    try:
        global memc
        key = str(uuid.uuid4())
        memc.set("poller.ping." + key, key, 60)
        if memc.get("poller.ping." + key) == key:
            memc.delete("poller.ping." + key)
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


# EOC0


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
        global servicedisco
        if servicedisco:
            if not IsNode:
                memc_touch("service.master", 10)
                nodes = memc.get("service.nodes")
                if nodes is None and not memc_alive():
                    print(
                        "WARNING: Lost Memcached. Taking over all devices. Nodes will quit shortly."
                    )
                    servicedisco = False
                    nodes = nodeso
                if nodes is not nodeso:
                    print("INFO: %s Node(s) Total" % (nodes))
                    nodeso = nodes
            else:
                memc_touch("service.nodes", 10)
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
        global service_devices
        real_duration += elapsed_time
        per_device_duration[device_id] = elapsed_time
        service_devices += 1
        if elapsed_time < 300:
            print(
                "INFO: worker %s finished device %s in %s seconds"
                % (worker_id, device_id, elapsed_time)
            )
        else:
            print(
                "WARNING: worker %s finished device %s in %s seconds"
                % (worker_id, device_id, elapsed_time)
            )
        print_queue.task_done()


"""
    This class will fork off single instances of the check-services.php process, record
    how long it takes, and push the resulting reports to the printer queue
"""


def poll_worker():
    while True:
        device_id = poll_queue.get()
        # (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC5
        if not servicedisco or memc.get("service.device." + str(device_id)) is None:
            if servicedisco:
                result = memc.add(
                    "service.device." + str(device_id),
                    config["distributed_poller_name"],
                    300,
                )
                if not result:
                    print(
                        "This device (%s) appears to be being service checked by another service node"
                        % (device_id)
                    )
                    poll_queue.task_done()
                    continue
                if not memc_alive() and IsNode:
                    print(
                        "Lost Memcached, Not service checking Device %s as Node. Master will check it."
                        % device_id
                    )
                    poll_queue.task_done()
                    continue
            # EOC5
            try:
                start_time = time.time()

                output = (
                    "-d >> %s/services_device_%s.log" % (log_dir, device_id)
                    if debug
                    else ">> /dev/null"
                )
                # TODO replace with command_runner
                command = "/usr/bin/env php %s -h %s %s 2>&1" % (
                    service_path,
                    device_id,
                    output,
                )
                subprocess.check_call(command, shell=True)

                elapsed_time = int(time.time() - start_time)
                print_queue.put(
                    [threading.current_thread().name, device_id, elapsed_time]
                )
            except (KeyboardInterrupt, SystemExit):
                raise
            except:
                pass
        poll_queue.task_done()


if __name__ == "__main__":
    logger = LNMS.logger_get_logger(LOG_FILE, debug=_DEBUG)

    install_dir = os.path.dirname(os.path.realpath(__file__))
    LNMS.check_for_file(install_dir + "/.env")
    config = json.loads(LNMS.get_config_data(install_dir))

    service_path = config["install_dir"] + "/check-services.php"
    log_dir = config["log_dir"]

    # (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC1
    if "distributed_poller_group" in config:
        service_group = str(config["distributed_poller_group"])
    else:
        service_group = False

    if (
        "distributed_poller" in config
        and "distributed_poller_memcached_host" in config
        and "distributed_poller_memcached_port" in config
        and config["distributed_poller"]
    ):
        try:
            import memcache
            import uuid

            memc = memcache.Client(
                [
                    config["distributed_poller_memcached_host"]
                    + ":"
                    + str(config["distributed_poller_memcached_port"])
                ]
            )
            if str(memc.get("service.master")) == config["distributed_poller_name"]:
                print("This system is already joined as the service master.")
                sys.exit(2)
            if memc_alive():
                if memc.get("service.master") is None:
                    print("Registered as Master")
                    memc.set("service.master", config["distributed_poller_name"], 10)
                    memc.set("service.nodes", 0, 300)
                    IsNode = False
                else:
                    print(
                        "Registered as Node joining Master %s"
                        % memc.get("service.master")
                    )
                    IsNode = True
                    memc.incr("service.nodes")
                servicedisco = True
            else:
                print(
                    "Could not connect to memcached, disabling distributed service checks."
                )
                servicedisco = False
                IsNode = False
        except SystemExit:
            raise
        except ImportError:
            print("ERROR: missing memcache python module:")
            print("On deb systems: apt-get install python3-memcache")
            print("On other systems: pip3 install python-memcached")
            print("Disabling distributed discovery.")
            servicedisco = False
    else:
        servicedisco = False
    # EOC1

    s_time = time.time()
    real_duration = 0
    per_device_duration = {}
    service_devices = 0

    """
        Take the amount of threads we want to run in parallel from the commandline
        if None are given or the argument was garbage, fall back to default of 16
    """
    usage = "usage: %prog [options] <workers> (Default: 1 (Do not set too high)"
    description = "Spawn multiple check-services.php processes in parallel."
    parser = OptionParser(usage=usage, description=description)
    parser.add_option(
        "-d",
        "--debug",
        action="store_true",
        default=False,
        help="Enable debug output. WARNING: Leaving this enabled will consume a lot of disk space.",
    )
    (options, args) = parser.parse_args()

    debug = options.debug
    try:
        amount_of_workers = int(args[0])
    except (IndexError, ValueError):
        amount_of_workers = 1

    devices_list = []

    # (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC2
    if service_group is not False:
        query = (
            "SELECT DISTINCT(`services`.`device_id`) FROM `services` LEFT JOIN `devices` ON `services`.`device_id` = `devices`.`device_id` WHERE `devices`.`poller_group` IN("
            + service_group
            + ") AND `devices`.`disabled` = 0"
        )
    else:
        query = "SELECT DISTINCT(`services`.`device_id`) FROM `services` LEFT JOIN `devices` ON `services`.`device_id` = `devices`.`device_id` WHERE `devices`.`disabled` = 0"
    # EOC2

    db = LNMS.db_open(
        config["db_socket"],
        config["db_host"],
        config["db_port"],
        config["db_user"],
        config["db_pass"],
        config["db_name"],
    )
    cursor = db.cursor()
    cursor.execute(query)
    devices = cursor.fetchall()
    for row in devices:
        devices_list.append(int(row[0]))
    # (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC3
    if servicedisco and not IsNode:
        query = "SELECT MAX(`device_id`), MIN(`device_id`) FROM `services`"
        cursor.execute(query)
        devices = cursor.fetchall()
        maxlocks = devices[0][0] or 0
        minlocks = devices[0][1] or 0
    # EOC3
    db.close()

    poll_queue = queue.Queue()
    print_queue = queue.Queue()

    print(
        "INFO: starting the service check at %s with %s threads"
        % (time.strftime("%Y-%m-%d %H:%M:%S"), amount_of_workers)
    )

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

    print(
        "INFO: services-wrapper checked %s devices in %s seconds with %s workers"
        % (service_devices, total_time, amount_of_workers)
    )

    # (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC6
    if servicedisco or memc_alive():
        master = memc.get("service.master")
        if master == config["distributed_poller_name"] and not IsNode:
            print("Wait for all service-nodes to finish")
            nodes = memc.get("service.nodes")
            while nodes is not None and nodes > 0:
                try:
                    time.sleep(1)
                    nodes = memc.get("service.nodes")
                except:
                    pass
            print("Clearing Locks")
            x = minlocks
            while x <= maxlocks:
                memc.delete("service.device." + str(x))
                x = x + 1
            print("%s Locks Cleared" % x)
            print("Clearing Nodes")
            memc.delete("service.master")
            memc.delete("service.nodes")
        else:
            memc.decr("service.nodes")
        print("Finished %s." % time.time())
    # EOC6

    show_stopper = False

    if total_time > 300:
        print(
            "WARNING: the process took more than 5 minutes to finish, you need faster hardware or more threads"
        )
        print(
            "INFO: in sequential style service checks the elapsed time would have been: %s seconds"
            % real_duration
        )
        for device in per_device_duration:
            if per_device_duration[device] > 300:
                print(
                    "WARNING: device %s is taking too long: %s seconds"
                    % (device, per_device_duration[device])
                )
                show_stopper = True
        if show_stopper:
            print(
                "ERROR: Some devices are taking more than 300 seconds, the script cannot recommend you what to do."
            )
        else:
            recommend = int(total_time / 300.0 * amount_of_workers + 1)
            print(
                "WARNING: Consider setting a minimum of %d threads. (This does not constitute professional advice!)"
                % recommend
            )

        sys.exit(2)
