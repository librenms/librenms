#! /usr/bin/env python3
#  -*- coding: utf-8 -*-

import sys
import os
import logging
import tempfile
import json

from logging.handlers import RotatingFileHandler
from LibreNMS.command_runner import command_runner


logger = logging.getLogger(__name__)

# Logging functions ########################################################

FORMATTER = logging.Formatter("%(asctime)s :: %(levelname)s :: %(message)s")


def logger_get_console_handler():
    try:
        console_handler = logging.StreamHandler(sys.stdout)
    except OSError as exc:
        print("Cannot log to stdout, trying stderr. Message %s" % exc)
        try:
            console_handler = logging.StreamHandler(sys.stderr)
            console_handler.setFormatter(FORMATTER)
            return console_handler
        except OSError as exc:
            print("Cannot log to stderr neither. Message %s" % exc)
            return False
    else:
        console_handler.setFormatter(FORMATTER)
        return console_handler


def logger_get_file_handler(log_file):
    err_output = None
    try:
        file_handler = RotatingFileHandler(
            log_file, mode="a", encoding="utf-8", maxBytes=1024000, backupCount=3
        )
    except OSError as exc:
        try:
            print(
                "Cannot create logfile. Trying to obtain temporary log file.\nMessage: %s"
                % exc
            )
            err_output = str(exc)
            temp_log_file = tempfile.gettempdir() + os.sep + __name__ + ".log"
            print("Trying temporary log file in " + temp_log_file)
            file_handler = RotatingFileHandler(
                temp_log_file,
                mode="a",
                encoding="utf-8",
                maxBytes=1000000,
                backupCount=1,
            )
            file_handler.setFormatter(FORMATTER)
            err_output += "\nUsing [%s]" % temp_log_file
            return file_handler, err_output
        except OSError as exc:
            print(
                "Cannot create temporary log file either. Will not log to file. Message: %s"
                % exc
            )
            return False
    else:
        file_handler.setFormatter(FORMATTER)
        return file_handler, err_output


def logger_get_logger(log_file=None, temp_log_file=None, debug=False):
    # If a name is given to getLogger, than modules can't log to the root logger
    _logger = logging.getLogger()
    if debug is True:
        _logger.setLevel(logging.DEBUG)
    else:
        _logger.setLevel(logging.INFO)
    console_handler = logger_get_console_handler()
    if console_handler:
        _logger.addHandler(console_handler)
    if log_file is not None:
        file_handler, err_output = logger_get_file_handler(log_file)
        if file_handler:
            _logger.addHandler(file_handler)
            _logger.propagate = False
            if err_output is not None:
                print(err_output)
                _logger.warning(
                    "Failed to use log file [%s], %s.", log_file, err_output
                )
    if temp_log_file is not None:
        if os.path.isfile(temp_log_file):
            try:
                os.remove(temp_log_file)
            except OSError:
                logger.warning("Cannot remove temp log file [%s]." % temp_log_file)
        file_handler, err_output = logger_get_file_handler(temp_log_file)
        if file_handler:
            _logger.addHandler(file_handler)
            _logger.propagate = False
            if err_output is not None:
                print(err_output)
                _logger.warning(
                    "Failed to use log file [%s], %s.", log_file, err_output
                )
    return _logger


# Generic functions ########################################################


def check_for_file(file):
    try:
        with open(file) as file:
            pass
    except IOError as exc:
        logger.error("Oh dear... %s does not seem readable: " % (file, exc))
        logger.debug("Traceback:", exc_info=True)
        sys.exit(2)


# Config functions #########################################################


def get_config_data(base_dir):
    check_for_file(os.path.join(base_dir, ".env"))

    try:
        import dotenv

        env_path = "{}/.env".format(base_dir)
        logger.info("Attempting to load .env from '%s'", env_path)
        dotenv.load_dotenv(dotenv_path=env_path, verbose=True)

        if not os.getenv("NODE_ID"):
            logger.critical(".env does not contain a valid NODE_ID setting.")

    except ImportError as e:
        logger.critical(
            "Could not import .env - check that the poller user can read the file, and that composer install has been run recently"
        )

    config_cmd = ["/usr/bin/env", "php", "%s/config_to_json.php" % base_dir]
    try:
        exit_code, output = command_runner(config_cmd)
        if exit_code == 0:
            return json.loads(output)
        raise EnvironmentError
    except Exception as exc:
        logger.critical("ERROR: Could not execute command [%s]: %s" % (config_cmd, exc))
        logger.debug("Traceback:", exc_info=True)
