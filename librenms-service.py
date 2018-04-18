#!/usr/bin/env python
import sys
import argparse
import logging

import LibreNMS

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='LibreNMS Service - manages polling and other periodic processes')
    parser.add_argument('-g', '--group', type=int, help="Set the poller group for this poller")
    parser.add_argument('-v', '--verbose', action='count', help="Show verbose output.")
    parser.add_argument('-d', '--debug', action="store_true", help="Show debug output.")
    parser.add_argument('-m', '--multiple', action="store_true", help="Allow multiple instances of the service.")
    parser.add_argument('-t', '--timestamps', action="store_true", help="Include timestamps in the logs (not normally needed for syslog/journald")

    args = parser.parse_args()

    if args.timestamps:
        logging.basicConfig(format='%(asctime)s %(threadName)s(%(levelname)s):%(message)s')
    else:
        logging.basicConfig(format='%(threadName)s(%(levelname)s):%(message)s')

    if args.verbose:
        logging.getLogger().setLevel(logging.INFO)

    if args.debug:
        logging.getLogger().setLevel(logging.DEBUG)

    try:
        service = LibreNMS.Service()
    except Exception as e:
        # catch any initialization errors and print the message instead of a stack trace
        print(e)
        sys.exit(2)

    service.config.single_instance = args.multiple

    if args.group:
        service.config.group = [ args.group ]

    print('Starting LibreNMS Service...')
    service.start()
