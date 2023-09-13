#! /usr/bin/env python3
"""
 wrapper        A small tool which wraps services, discovery and poller php scripts
                in order to run them as threads with Queue and workers

 Authors:       Orsiris de Jong <contact@netpower.fr>
                Neil Lathwood <neil@librenms.org>
                Job Snijders <job.snijders@atrato.com>

                Distributed poller code (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org>
                All code parts that belong to Daniel are enclosed in EOC comments

 Date:          Sep 2021

 Usage:         This program accepts three command line arguments
                - the number of threads (defaults to 1 for discovery / service, and 16 for poller)
                - the wrapper type (service, discovery or poller)
                - optional debug boolean


 Ubuntu Linux:  apt-get install python-mysqldb
 FreeBSD:       cd /usr/ports/*/py-MySQLdb && make install clean
 RHEL 7:        yum install MySQL-python
 RHEL 8:        dnf install mariadb-connector-c-devel gcc && python -m pip install mysqlclient

 Tested on:     Python 3.6.8 / PHP 7.2.11 / CentOS 8 / AlmaLinux 8.4

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

import logging
import os
import queue
import re
import sys
import threading
import time
import uuid
from argparse import ArgumentParser

import LibreNMS
from LibreNMS.command_runner import command_runner
from LibreNMS.config import DBConfig


logger = logging.getLogger(__name__)

# Timeout in seconds for any poller / service / discovery action per device
# Should be higher than stepping which defaults to 300
PER_DEVICE_TIMEOUT = 900

# 5 = no new discovered devices, 6 = unreachable device
VALID_EXIT_CODES = [0, 5, 6]


DISTRIBUTED_POLLING = False  # Is overriden by config.php
REAL_DURATION = 0
DISCOVERED_DEVICES_COUNT = 0
PER_DEVICE_DURATION = {}
ERRORS = 0

MEMC = None
IS_NODE = None
STEPPING = None
MASTER_TAG = None
NODES_TAG = None
TIME_TAG = ""

"""
Per wrapper type configuration
All time related variables are in seconds
"""
wrappers = {
    "service": {
        "executable": "check-services.php",
        "table_name": "services",
        "memc_touch_time": 10,
        "stepping": 300,
        "nodes_stepping": 300,
        "total_exec_time": 300,
    },
    "discovery": {
        "executable": "discovery.php",
        "table_name": "devices",
        "memc_touch_time": 30,
        "stepping": 300,
        "nodes_stepping": 3600,
        "total_exec_time": 21600,
    },
    "poller": {
        "executable": "poller.php",
        "table_name": "devices",
        "memc_touch_time": 10,
        "stepping": 300,
        "nodes_stepping": 300,
        "total_exec_time": 300,
    },
}

"""
 Threading helper functions
"""


#  <<<EOC
def memc_alive(name):  # Type: str
    """
    Checks if memcache is working by injecting a random string and trying to read it again
    """
    try:
        key = str(uuid.uuid4())
        MEMC.set(name + ".ping." + key, key, 60)
        if MEMC.get(name + ".ping." + key) == key:
            MEMC.delete(name + ".ping." + key)
            return True
        return False
    except:
        return False


def memc_touch(key, _time):  # Type: str  # Type: int
    """
    Updates a memcache key wait time
    """
    try:
        val = MEMC.get(key)
        MEMC.set(key, val, _time)
    except:
        pass


def get_time_tag(step):  # Type: int
    """
    Get current time tag as timestamp module stepping
    """
    timestamp = int(time.time())
    return timestamp - timestamp % step


# EOC


def print_worker(print_queue, wrapper_type):  # Type: Queue  # Type: str
    """
    A seperate queue and a single worker for printing information to the screen prevents
    the good old joke:

        Some people, when confronted with a problem, think,
        "I know, I'll use threads," and then they have two problems.
    """
    nodeso = 0
    while True:
        #  <<<EOC
        global IS_NODE
        global DISTRIBUTED_POLLING
        if DISTRIBUTED_POLLING:
            if not IS_NODE:
                memc_touch(MASTER_TAG, wrappers[wrapper_type]["memc_touch_time"])
                nodes = MEMC.get(NODES_TAG)
                if nodes is None and not memc_alive(wrapper_type):
                    logger.warning(
                        "Lost Memcached. Taking over all devices. Nodes will quit shortly."
                    )
                    DISTRIBUTED_POLLING = False
                    nodes = nodeso
                if nodes is not nodeso:
                    logger.info(f"{nodes} Node(s) Total")
                    nodeso = nodes
            else:
                memc_touch(NODES_TAG, wrappers[wrapper_type]["memc_touch_time"])
            try:
                (
                    worker_id,
                    device_id,
                    elapsed_time,
                    command,
                    exit_code,
                ) = print_queue.get(False)
            except:
                pass
                try:
                    time.sleep(1)
                except:
                    pass
                continue
        else:
            worker_id, device_id, elapsed_time, command, exit_code = print_queue.get()
        # EOC

        global REAL_DURATION
        global PER_DEVICE_DURATION
        global DISCOVERED_DEVICES_COUNT

        REAL_DURATION += elapsed_time
        PER_DEVICE_DURATION[device_id] = elapsed_time
        DISCOVERED_DEVICES_COUNT += 1
        if elapsed_time < STEPPING and exit_code in VALID_EXIT_CODES:
            logger.info(
                f"worker {worker_id} finished device {device_id} "
                f"in {elapsed_time} seconds"
            )
        else:
            logger.warning(
                f"worker {worker_id} finished device {device_id} "
                f"in {worker_id} seconds with exit code {exit_code}"
            )
            logger.debug(f"Command was {command}")
        print_queue.task_done()


def poll_worker(
    poll_queue,  # Type: Queue
    print_queue,  # Type: Queue
    config,  # Type: dict
    log_dir,  # Type: str
    wrapper_type,  # Type: str
    debug,  # Type: bool
    modules="",  # Type: string
):
    """
    This function will fork off single instances of the php process, record
    how long it takes, and push the resulting reports to the printer queue
    """

    global ERRORS

    while True:
        device_id = poll_queue.get()
        #  <<<EOC
        if (
            not DISTRIBUTED_POLLING
            or MEMC.get(f"{wrapper_type}.device.{device_id}{TIME_TAG}") is None
        ):
            if DISTRIBUTED_POLLING:
                result = MEMC.add(
                    f"{wrapper_type}.device.{device_id}{TIME_TAG}",
                    config["distributed_poller_name"],
                    STEPPING,
                )
                if not result:
                    logger.info(
                        f"The device {device_id} appears to be being checked by another node"
                    )
                    poll_queue.task_done()
                    continue
                if not memc_alive(wrapper_type) and IS_NODE:
                    logger.warning(
                        f"Lost Memcached, Not checking Device {device_id} as Node. Master will check it."
                    )
                    poll_queue.task_done()
                    continue
            # EOC
            try:
                start_time = time.time()

                device_log = os.path.join(
                    log_dir, f"{wrapper_type}_device_{device_id}.log"
                )
                executable = os.path.join(
                    os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                    wrappers[wrapper_type]["executable"],
                )
                command = f"/usr/bin/env php {executable} -h {device_id}"
                if modules is not None and len(str(modules).strip()):
                    module_str = re.sub("\s", "", str(modules).strip())
                    command = command + f" -m {module_str}"
                if debug:
                    command = command + " -d"
                exit_code, output = command_runner(
                    command,
                    shell=True,
                    timeout=PER_DEVICE_TIMEOUT,
                    valid_exit_codes=VALID_EXIT_CODES,
                )
                if exit_code not in [0, 6]:
                    thread_name = threading.current_thread().name
                    logger.error(
                        f"Thread {thread_name} exited with code {exit_code}"
                    )
                    ERRORS += 1
                    logger.error(output)
                elif exit_code == 5:
                    logger.info(f"Unreachable device {device_id}")
                else:
                    logger.debug(output)
                if debug:
                    with open(device_log, "w", encoding="utf-8") as dev_log_file:
                        dev_log_file.write(output)

                elapsed_time = int(time.time() - start_time)
                print_queue.put(
                    [
                        threading.current_thread().name,
                        device_id,
                        elapsed_time,
                        command,
                        exit_code,
                    ]
                )
            except (KeyboardInterrupt, SystemExit):
                raise
            except Exception:
                logger.error("Unknown problem happened: ")
                logger.error("Traceback:", exc_info=True)
        poll_queue.task_done()


def wrapper(
    wrapper_type,  # Type: str
    amount_of_workers,  # Type: int
    config,  # Type: dict
    log_dir,  # Type: str
    _debug=False,  # Type: bool
    **kwargs,  # Type: dict, may contain: modules, groups [=device groups]
):  # -> None
    """
    Actual code that runs various php scripts, in single node mode or distributed poller mode
    """

    global MEMC
    global IS_NODE
    global DISTRIBUTED_POLLING
    global MASTER_TAG
    global NODES_TAG
    global TIME_TAG
    global STEPPING

    # Setup wrapper dependent variables
    STEPPING = wrappers[wrapper_type]["stepping"]
    if wrapper_type == "poller":
        if "rrd" in config and "step" in config["rrd"]:
            STEPPING = config["rrd"]["step"]
        TIME_TAG = "." + str(get_time_tag(STEPPING))

    MASTER_TAG = f"{wrapper_type}.master{TIME_TAG}"
    NODES_TAG = f"{wrapper_type}.nodes{TIME_TAG}"

    #  <<<EOC
    if "distributed_poller_group" in config:
        poller_group = str(config["distributed_poller_group"])
    else:
        poller_group = False

    if (
        "distributed_poller" in config
        and "distributed_poller_memcached_host" in config
        and "distributed_poller_memcached_port" in config
        and config["distributed_poller"]
    ):
        try:
            import memcache

            MEMC = memcache.Client(
                [
                    config["distributed_poller_memcached_host"]
                    + ":"
                    + str(config["distributed_poller_memcached_port"])
                ]
            )
            if str(MEMC.get(MASTER_TAG)) == config["distributed_poller_name"]:
                logger.info("This system is already joined as the service master.")
                sys.exit(2)
            if memc_alive(wrapper_type):
                if MEMC.get(MASTER_TAG) is None:
                    logger.info("Registered as Master")
                    MEMC.set(MASTER_TAG, config["distributed_poller_name"], 10)
                    MEMC.set(NODES_TAG, 0, wrappers[wrapper_type]["nodes_stepping"])
                    IS_NODE = False
                else:
                    logger.info(
                        f"Registered as Node joining Master {MEMC.get(MASTER_TAG)}"
                    )
                    IS_NODE = True
                    MEMC.incr(NODES_TAG)
                DISTRIBUTED_POLLING = True
            else:
                logger.warning(
                    "Could not connect to memcached, disabling distributed service checks."
                )
                DISTRIBUTED_POLLING = False
                IS_NODE = False
        except SystemExit:
            raise
        except ImportError:
            logger.critical("ERROR: missing memcache python module:")
            logger.critical("On deb systems: apt-get install python3-memcache")
            logger.critical("On other systems: pip3 install python-memcached")
            logger.critical("Disabling distributed discovery.")
            DISTRIBUTED_POLLING = False
    else:
        DISTRIBUTED_POLLING = False
    # EOC

    s_time = time.time()

    devices_list = []

    query_args = []
    dg_where_expansion = ""
    # if device groups were passed, build an additional where condition here
    d_groups = kwargs.get("groups")

    if kwargs.get("groups"):
        # remove trailing comma whitespace combinations
        cleaned_groups = re.sub("(^,|,$)", "", str(d_groups).strip())
        device_groups = re.split(",", cleaned_groups)
        group_where_items = []
        for group in device_groups:
            group = group.strip()
            if group.isnumeric():
                query_args.append(int(group))
                group_where_items.append("device_groups.id = %s")
            else:
                query_args.append(group.replace("*", "%"))
                group_where_items.append("device_groups.name LIKE %s")
        if group_where_items:
            group_where = " OR ".join(group_where_items)
            dg_where_expansion = f"AND ({group_where})"

    if wrapper_type == "service":
        #  <<<EOC
        if poller_group is not False:
            query = (
                "SELECT DISTINCT(services.device_id) FROM services "
                "LEFT JOIN devices"
                " ON services.device_id = devices.device_id "
                "LEFT JOIN device_group_device"
                " ON devices.device_id = device_group_device.device_id "
                "LEFT JOIN device_groups"
                " ON device_group_device.device_group_id = device_groups.id "
                f"WHERE devices.poller_group IN(%s) {dg_where_expansion} "
                "AND devices.disabled = 0"
            )
            query_args.insert(0, poller_group)
        else:
            query = (
                "SELECT DISTINCT(services.device_id) FROM services "
                "LEFT JOIN devices ON services.device_id = devices.device_id "
                "LEFT JOIN device_group_device"
                " ON devices.device_id = device_group_device.device_id "
                "LEFT JOIN device_groups"
                " ON device_group_device.device_group_id = device_groups.id "
                f"WHERE devices.disabled = 0 {dg_where_expansion}"
            )
        # EOC
    elif wrapper_type in ["discovery", "poller"]:
        """
        This query specificly orders the results depending on the last_discovered_timetaken variable
        Because this way, we put the devices likely to be slow, in the top of the queue
        thus greatening our chances of completing _all_ the work in exactly the time it takes to
        discover the slowest device! cool stuff he
        """
        #  <<<EOC
        if poller_group is not False:
            query = (
                "SELECT device_id FROM devices "
                "LEFT JOIN device_group_device"
                " ON devices.device_id = device_group_device.device_id "
                "LEFT JOIN device_groups"
                " ON device_group_device.device_group_id = device_groups.id "
                f"WHERE poller_group IN (%s) {dg_where_expansion} "
                "AND disabled = 0 ORDER BY last_polled_timetaken DESC"
            )
            query_args.insert(0, poller_group)
        else:
            query = (
                "SELECT device_id FROM devices "
                f"WHERE disabled = 0 {dg_where_expansion} "
                "ORDER BY last_polled_timetaken DESC"
            )
        # EOC
    else:
        logger.critical("Bogus wrapper type called")
        sys.exit(3)

    sconfig = DBConfig()
    sconfig.populate(config)
    db_connection = LibreNMS.DB(sconfig)
    cursor = db_connection.query(query)
    devices = cursor.fetchall()
    for row in devices:
        devices_list.append(int(row[0]))

    #  <<<EOC
    if DISTRIBUTED_POLLING and not IS_NODE:
        table_name = wrappers[wrapper_type]["table_name"]
        query = f"SELECT max(device_id),min(device_id) FROM {table_name}"
        cursor = db_connection.query(query)
        devices = cursor.fetchall()
        maxlocks = devices[0][0] or 0
        minlocks = devices[0][1] or 0
    # EOC

    poll_queue = queue.Queue()
    print_queue = queue.Queue()

    # Don't have more threads than workers
    amount_of_devices = len(devices_list)
    if amount_of_workers > amount_of_devices:
        amount_of_workers = amount_of_devices

    log_datetime = time.strftime("%Y-%m-%d %H:%M:%S")
    logger_str = (
        f"starting the {wrapper_type} check at {log_datetime} "
        f"with {amount_of_workers} threads for {amount_of_devices} devices"
    )
    logger.info(logger_str)

    for device_id in devices_list:
        poll_queue.put(device_id)

    for _ in range(amount_of_workers):
        worker = threading.Thread(
            target=poll_worker,
            kwargs={
                "poll_queue": poll_queue,
                "print_queue": print_queue,
                "config": config,
                "log_dir": log_dir,
                "wrapper_type": wrapper_type,
                "debug": _debug,
                "modules": kwargs.get("modules", ""),
            },
        )
        worker.setDaemon(True)
        worker.start()

    pworker = threading.Thread(
        target=print_worker,
        kwargs={"print_queue": print_queue, "wrapper_type": wrapper_type},
    )
    pworker.setDaemon(True)
    pworker.start()

    try:
        poll_queue.join()
        print_queue.join()
    except (KeyboardInterrupt, SystemExit):
        raise

    total_time = int(time.time() - s_time)

    end_msg = (
        f"{wrapper_type}-wrapper checked {DISCOVERED_DEVICES_COUNT} devices "
        f"in {total_time} seconds with {amount_of_workers} workers "
        f"with {ERRORS} errors"
    )
    if ERRORS == 0:
        logger.info(end_msg)
    else:
        logger.error(end_msg)

    #  <<<EOC
    if DISTRIBUTED_POLLING or memc_alive(wrapper_type):
        master = MEMC.get(MASTER_TAG)
        if master == config["distributed_poller_name"] and not IS_NODE:
            logger.info("Wait for all service-nodes to finish")
            nodes = MEMC.get(NODES_TAG)
            while nodes is not None and nodes > 0:
                try:
                    time.sleep(1)
                    nodes = MEMC.get(NODES_TAG)
                except:
                    pass
            logger.info(f"Clearing Locks for {NODES_TAG}")
            x = minlocks
            while x <= maxlocks:
                MEMC.delete(f"{wrapper_type}.device.{x}")
                x = x + 1
            logger.info(f"{x} Locks Cleared")
            logger.info("Clearing Nodes")
            MEMC.delete(MASTER_TAG)
            MEMC.delete(NODES_TAG)
        else:
            MEMC.decr(NODES_TAG)
        logger.info(f"Finished {time.strftime('%Y-%m-%d %H:%M:%S')}")
    # EOC

    # Update poller statistics
    if wrapper_type == "poller":
        poller_name = config["distributed_poller_name"]
        query = (
            "UPDATE pollers SET last_polled=NOW(), "
            f"devices='{DISCOVERED_DEVICES_COUNT}', time_taken='{total_time}' "
            f"WHERE poller_name='{poller_name}'"
        )
        cursor = db_connection.query(query)
        if cursor.rowcount < 1:
            query = (
                f"INSERT INTO pollers SET poller_name='{poller_name}', "
                f"last_polled=NOW(), devices='{DISCOVERED_DEVICES_COUNT}', "
                f"time_taken='{total_time}'"
            )
            db_connection.query(query)

    db_connection.close()

    w_total_exec_time = wrappers[wrapper_type]["total_exec_time"]
    if total_time > w_total_exec_time:
        logger.warning(
            f"the process took more than {w_total_exec_time} seconds to "
            "finish, you need faster hardware or more threads"
        )
        logger.warning(
            "in sequential style service checks the elapsed time "
            f"would have been: {REAL_DURATION} seconds"
        )
        show_stopper = False
        for device in PER_DEVICE_DURATION:
            if PER_DEVICE_DURATION[device] > wrappers[wrapper_type]["nodes_stepping"]:
                logger.warning(
                    f"device {device} is taking too long: "
                    f"{PER_DEVICE_DURATION[device]} seconds"
                )
                show_stopper = True
        if show_stopper:
            nodes_stepping = wrappers[wrapper_type]["nodes_stepping"]
            logger.error(
                f"Some devices are taking more than {nodes_stepping} seconds, "
                "the script cannot recommend you what to do."
            )
        else:
            recommend = int(total_time / STEPPING * amount_of_workers + 1)
            logger.warning(
                f"Consider setting a minimum of {recommend} threads. "
                f"(This does not constitute professional advice!)"
            )
        sys.exit(2)


if __name__ == "__main__":
    parser = ArgumentParser(
        prog="wrapper.py",
        usage="usage: %(prog)s [options] <wrapper_type> <workers>\n"
        "wrapper_type = 'service', 'poller' or 'disccovery'"
        "workers defaults to 1 for service and discovery, and 16 for poller "
        "(Do not set too high, or you will get an OOM)",
        description="Spawn multiple librenms php processes in parallel.",
    )
    parser.add_argument(
        "-d",
        "--debug",
        action="store_true",
        default=False,
        help="Enable debug output. WARNING: Leaving this enabled will consume a lot of disk space.",
    )
    parser.add_argument(
        "-m",
        "--modules",
        default="",
        help="Enable passing of a module string, modules are separated by comma",
    )

    parser.add_argument(
        dest="wrapper",
        default=None,
        help="Execute wrapper for 'service', 'poller' or 'discovery'",
    )
    parser.add_argument(
        dest="threads", action="store_true", default=None, help="Number of workers"
    )

    args = parser.parse_args()

    debug = args.debug
    modules = args.modules or ""
    wrapper_type = args.wrapper
    amount_of_workers = args.threads

    if wrapper_type not in ["service", "discovery", "poller"]:
        parser.error(f"Invalid wrapper type '{wrapper_type}'")
        sys.exit(4)

    config = LibreNMS.get_config_data(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__)))
    )
    log_dir = config["log_dir"]
    log_file = os.path.join(log_dir, wrapper_type + ".log")
    logger = LibreNMS.logger_get_logger(log_file, debug=debug)

    try:
        amount_of_workers = int(amount_of_workers)
    except (IndexError, ValueError, TypeError):
        amount_of_workers = (
            16 if wrapper_type == "poller" else 1
        )  # Defaults to 1 for service/discovery, 16 for poller
        logger.warning(
            f"Bogus number of workers given. "
            f"Using default number ({amount_of_workers}) of workers."
        )

    if wrapper_type in ["discovery", "poller"]:
        modules_validated = modules
    else:
        modules_validated = ""  # ignore module parameter

    wrapper(
        wrapper_type,
        amount_of_workers,
        config,
        log_dir,
        _debug=debug,
        modules=modules_validated,
    )
