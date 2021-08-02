#! /usr/bin/env python3
"""
This is a Bootstrap script for wrapper.py, in order to retain compatibility with earlier LibreNMS setups
"""

import os
from optparse import OptionParser

import LibreNMS.library as lnms
import LibreNMS.wrapper as wrapper

WRAPPER_TYPE = "discovery"
DEFAULT_WORKERS = 16

"""
    Take the amount of threads we want to run in parallel from the commandline
    if None are given or the argument was garbage, fall back to default of 1
"""
usage = "usage: %prog [options] <workers> (Default: 1 Do not set too high)"
description = "Spawn multiple discovery.php processes in parallel."
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

config = wrapper.get_config(os.path.dirname(os.path.realpath(__file__)))
log_dir = config["log_dir"]
log_file = os.path.join(log_dir, WRAPPER_TYPE + ".log")
logger = lnms.logger_get_logger(log_file, debug=debug)

try:
    amount_of_workers = int(args[0])
except (IndexError, ValueError):
    amount_of_workers = DEFAULT_WORKERS
    logger.warning(
        "Bogus number of workers given. Using default number ({}) of workers.".format(
            amount_of_workers
        )
    )

wrapper.wrapper(
    WRAPPER_TYPE,
    amount_of_workers=amount_of_workers,
    config=config,
    log_dir=log_dir,
    _debug=debug,
)
