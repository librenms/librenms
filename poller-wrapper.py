#! /usr/bin/env python3
"""
This is a Bootstrap script for wrapper.py, in order to retain compatibility with earlier LibreNMS setups
"""

import os
import sys
import logging
from argparse import ArgumentParser

import LibreNMS
import LibreNMS.wrapper as wrapper

WRAPPER_TYPE = "poller"
DEFAULT_WORKERS = 16
DEFAULT_LOCKWAIT = 20

"""
    Take the amount of threads we want to run in parallel from the commandline
    if None are given or the argument was garbage, fall back to default
"""
usage = (
    "usage: %(prog)s [options] <amount_of_workers> (Default: {}"
    "(Do not set too high, or you will get an OOM)".format(DEFAULT_WORKERS)
)
description = "Spawn multiple poller.php processes in parallel."
parser = ArgumentParser(usage=usage, description=description)
parser.add_argument(dest="amount_of_workers", default=DEFAULT_WORKERS)
parser.add_argument(
    "-d",
    "--debug",
    dest="debug",
    action="store_true",
    default=False,
    help="Enable debug output. WARNING: Leaving this enabled will consume a lot of disk space.",
)
parser.add_argument(
    "-p",
    "--persistent",
    dest="persistent",
    action="store_true",
    default=False,
    help="Use persistent poller processes to avoid the overhead of forking for each device.",
)
parser.add_argument(
    "-a",
    "--adaptive",
    dest="adaptive",
    action="store_true",
    default=False,
    help="Use a dynamic number of workers up to a maximum of <amount of workers> to try and spread the CPU load evenly over the polling cycle.",
)
parser.add_argument(
    "-l",
    "--lockfile",
    dest="lockfile",
    action="store",
    default=None,
    help="Specify a lockfile to ensure that only 1 process runs at a time.",
)
parser.add_argument(
    "-w",
    "--max-wait",
    dest="maxwait",
    action="store",
    default=DEFAULT_LOCKWAIT,
    help="Maximu wait time for the file lock as a percentage of the stepping interval.",
)
args = parser.parse_args()

config = LibreNMS.get_config_data(os.path.dirname(os.path.realpath(__file__)))
if not config:
    logger = logging.getLogger(__name__)
    logger.critical("Could not run {} wrapper. Missing config".format(WRAPPER_TYPE))
    sys.exit(1)
log_dir = config["log_dir"]
log_file = os.path.join(log_dir, WRAPPER_TYPE + "_wrapper.log")
logger = LibreNMS.logger_get_logger(log_file, debug=args.debug)

try:
    amount_of_workers = int(args.amount_of_workers)
except (IndexError, ValueError):
    amount_of_workers = DEFAULT_WORKERS
    logger.warning(
        "Bogus number of workers given. Using default number ({}) of workers.".format(
            amount_of_workers
        )
    )

try:
    lockwait = int(args.maxwait)
except (IndexError, ValueError):
    lockwait = DEFAULT_LOCKWAIT
    logger.warning(
        "Bogus lock wait time given. Using default {}% of step time.".format(
            lockwait
        )
    )

if lockwait < 0 or lockwait >= 100:
    lockwait = DEFAULT_LOCKWAIT
    logger.warning(
        "Bogus lock wait time given. Using default {}% of step time.".format(
            lockwait
        )
    )

wrapper.wrapper(
    WRAPPER_TYPE,
    amount_of_workers=amount_of_workers,
    config=config,
    log_dir=log_dir,
    _debug=args.debug,
    _lockfile=args.lockfile,
    _lockwait=lockwait,
    _persistent=args.persistent,
    _adaptive=args.adaptive,
)
