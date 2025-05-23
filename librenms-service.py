#!/usr/bin/env python3

import argparse
import logging
import os
import sys
import threading

import LibreNMS

logger = logging.getLogger(__name__)

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
        help="Log poller output to files. Warning: This could use significant disk space!",
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
        help="Include timestamps in the logs (not normally needed for syslog/journald)",
    )
    parser.add_argument(
        "--log-format",
        choices=["plain", "kv", "json"],
        default="plain",
        help="Output format: plain text, key=val, or JSON",
    )

    args = parser.parse_args()

    # Configure logging handler based on selected format
    root = logging.getLogger()
    handler = logging.StreamHandler()

    if args.log_format == "plain":
        fmt = "%(threadName)s(%(levelname)s): %(message)s"
        if args.timestamps:
            fmt = "%(asctime)s " + fmt
        handler.setFormatter(logging.Formatter(fmt, "%Y-%m-%dT%H:%M:%S%z"))

    elif args.log_format == "kv":
        kv_fmt = (
            "ts=%(asctime)s "
            "level=%(levelname)s "
            "thread=%(threadName)s "
            'msg="%(message)s"'
        )
        handler.setFormatter(logging.Formatter(kv_fmt, "%Y-%m-%dT%H:%M:%S%z"))

    else:  # json
        import json

        class JsonFormatter(logging.Formatter):
            def format(self, record):
                payload = {
                    "ts": self.formatTime(record, "%Y-%m-%dT%H:%M:%S%z"),
                    "level": record.levelname.lower(),
                    "thread": record.threadName,
                    "msg": record.getMessage(),
                }
                return json.dumps(payload)

        handler.setFormatter(JsonFormatter())

    # Replace default handlers
    root.handlers.clear()
    root.addHandler(handler)

    # Set log level (also on the handler so it filters correctly)
    if args.verbose:
        level = logging.INFO
    elif args.debug:
        level = logging.DEBUG
    else:
        level = logging.WARNING

    root.setLevel(level)
    handler.setLevel(level)

    logger.info("Configuring LibreNMS service")

    try:
        service = LibreNMS.Service()
    except Exception as e:
        # catch any initialization errors and print the message instead of a stack trace
        print(e)
        sys.exit(2)

    service.config.single_instance = args.multiple
    service.config.log_output = args.log_output

    if args.group:
        service.config.group = (
            args.group if isinstance(args.group, list) else [args.group]
        )

    logger.info(
        "Entering main LibreNMS service loop on {}/{}...".format(
            os.getpid(), threading.current_thread().name
        )
    )
    service.start()
