#!/usr/bin/env python3

import argparse
import logging
import os
import sys
import threading
from logging import info

import LibreNMS

if __name__ == "__main__":
    parser = argparse.ArgumentParser(
        description="LibreNMS Service - manages polling and other periodic processes"
    )
    parser.add_argument(
        "-g",
        "--group",
        nargs="+",
        type=int,
        help="Set the poller group for this poller",
    )
    parser.add_argument("-v", "--verbose", action="count", help="Show verbose output.")
    parser.add_argument("-d", "--debug", action="store_true", help="Show debug output.")
    parser.add_argument(
        "-o",
        "--log-output",
        action="store_true",
        help="Log poller ouput to files. Warning: This could use significant disk space!",
    )
    parser.add_argument(
        "-m",
        "--multiple",
        action="store_true",
        help="Allow multiple instances of the service.",
    )
    parser.add_argument(
        "-t",
        "--timestamps",
        action="store_true",
        help="Include timestamps in the logs (not normally needed for syslog/journald",
    )

    args = parser.parse_args()

    if args.timestamps:
        logging.basicConfig(
            format="%(asctime)s %(threadName)s(%(levelname)s):%(message)s"
        )
    else:
        logging.basicConfig(format="%(threadName)s(%(levelname)s):%(message)s")

    if args.verbose:
        logging.getLogger().setLevel(logging.INFO)
    elif args.debug:
        logging.getLogger().setLevel(logging.DEBUG)
    else:
        logging.getLogger().setLevel(logging.WARNING)

    info("Configuring LibreNMS service")
    try:
        service = LibreNMS.Service()
    except Exception as e:
        # catch any initialization errors and print the message instead of a stack trace
        print(e)
        sys.exit(2)

    service.config.single_instance = args.multiple
    service.config.log_output = args.log_output

    if args.group:
        if isinstance(args.group, list):
            service.config.group = args.group
        else:
            service.config.group = [args.group]

    info(
        "Entering main LibreNMS service loop on {}/{}...".format(
            os.getpid(), threading.current_thread().name
        )
    )
    service.start()
