#! /usr/bin/env python3
#  -*- coding: utf-8 -*-

import sys
import os
import logging
import tempfile
import subprocess
import threading
import time
from logging.handlers import RotatingFileHandler

try:
    import MySQLdb
except ImportError:
    try:
        import pymysql
        pymysql.install_as_MySQLdb()
        import MySQLdb
    except ImportError as exc:
        print('ERROR: missing the mysql python module please run:')
        print('pip install -r requirements.txt')
        print('ERROR: %s' % exc)
        sys.exit(2)

logger = logging.getLogger(__name__)

# Logging functions ########################################################
# The following logging functions are
# (C) 2019-2020 Orsiris de Jong under BSD 3-Clause license

FORMATTER = logging.Formatter('%(asctime)s :: %(levelname)s :: %(message)s')


def logger_get_console_handler():
    try:
        console_handler = logging.StreamHandler(sys.stdout)
    except OSError as exc:
        print('Cannot log to stdout, trying stderr. Message %s' % exc)
        try:
            console_handler = logging.StreamHandler(sys.stderr)
            console_handler.setFormatter(FORMATTER)
            return console_handler
        except OSError as exc:
            print('Cannot log to stderr neither. Message %s' % exc)
            return False
    else:
        console_handler.setFormatter(FORMATTER)
        return console_handler


def logger_get_file_handler(log_file):
    err_output = None
    try:
        file_handler = RotatingFileHandler(log_file, mode='a', encoding='utf-8', maxBytes=1024000, backupCount=3)
    except OSError as exc:
        try:
            print('Cannot create logfile. Trying to obtain temporary log file.\nMessage: %s' % exc)
            err_output = str(exc)
            temp_log_file = tempfile.gettempdir() + os.sep + __name__ + '.log'
            print('Trying temporary log file in ' + temp_log_file)
            file_handler = RotatingFileHandler(temp_log_file, mode='a', encoding='utf-8', maxBytes=1000000,
                                               backupCount=1)
            file_handler.setFormatter(FORMATTER)
            err_output += '\nUsing [%s]' % temp_log_file
            return file_handler, err_output
        except OSError as exc:
            print('Cannot create temporary log file either. Will not log to file. Message: %s' % exc)
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
                _logger.warning('Failed to use log file [%s], %s.', log_file, err_output)
    if temp_log_file is not None:
        if os.path.isfile(temp_log_file):
            try:
                os.remove(temp_log_file)
            except OSError:
                logger.warning('Cannot remove temp log file [%s].' % temp_log_file)
        file_handler, err_output = logger_get_file_handler(temp_log_file)
        if file_handler:
            _logger.addHandler(file_handler)
            _logger.propagate = False
            if err_output is not None:
                print(err_output)
                _logger.warning('Failed to use log file [%s], %s.', log_file, err_output)
    return _logger

# End of Logging functions #################################################

# Generic functions ########################################################

def command_runner(command, valid_exit_codes=None, timeout=300, shell=False, encoding='utf-8',
                   windows_no_window=False, **kwargs):
    """
    Unix & Windows compatible subprocess wrapper that handles encoding, timeout, and
    various exit codes.
    Accepts subprocess.check_output and subprocess.popen arguments
    Whenever we can, we need to avoid shell=True in order to preseve better security
    Runs system command, returns exit code and stdout/stderr output, and logs output on error
    valid_exit_codes is a list of codes that don't trigger an error
    
    (C) 2019-2020 Orsiris de Jong under BSD 3-Clause license
    """

    # Set default values for kwargs
    errors = kwargs.pop('errors', 'backslashreplace')  # Don't let encoding issues make you mad
    universal_newlines = kwargs.pop('universal_newlines', False)
    creationflags = kwargs.pop('creationflags', 0)
    if windows_no_window:
        # Disable the following pylint error since the code also runs on nt platform, but
        # triggers and error on Unix
        # pylint: disable=E1101
        creationflags = creationflags | subprocess.CREATE_NO_WINDOW

    try:
        # universal_newlines=True makes netstat command fail under windows
        # timeout does not work under Python 2.7 with subprocess32 < 3.5
        # decoder may be unicode_escape for dos commands or utf-8 for powershell
        # Disabling pylint error for the same reason as above
        # pylint: disable=E1123
        output = subprocess.check_output(command, stderr=subprocess.STDOUT, shell=shell,
                                         timeout=timeout, universal_newlines=universal_newlines, encoding=encoding,
                                         errors=errors, creationflags=creationflags, **kwargs)

    except subprocess.CalledProcessError as exc:
        exit_code = exc.returncode
        try:
            output = exc.output
        except Exception:
            output = "command_runner: Could not obtain output from command."
        if exit_code in valid_exit_codes if valid_exit_codes is not None else [0]:
            logger.debug('Command [%s] returned with exit code [%s]. Command output was:' % (command, exit_code))
            if isinstance(output, str):
                logger.debug(output)
            return exc.returncode, output
        else:
            logger.error('Command [%s] failed with exit code [%s]. Command output was:' %
                         (command, exc.returncode))
            logger.error(output)
            return exc.returncode, output
    # OSError if not a valid executable
    except (OSError, IOError) as exc:
        logger.error('Command [%s] failed because of OS [%s].' % (command, exc))
        return None, exc
    except subprocess.TimeoutExpired:
        logger.error('Timeout [%s seconds] expired for command [%s] execution.' % (timeout, command))
        return None, 'Timeout of %s seconds expired.' % timeout
    except Exception as exc:
        logger.error('Command [%s] failed for unknown reasons [%s].' % (command, exc))
        logger.debug('Error:', exc_info=True)
        return None, exc
    else:
        logger.debug('Command [%s] returned with exit code [0]. Command output was:' % command)
        if output:
            logger.debug(output)
        return 0, output


def check_for_file(file):
    try:
        with open(file) as f:
            pass
    except IOError as exc:
        logger.error('Oh dear... %s does not seem readable' % file)
        logger.debug('ERROR:', exc_info=True)
        sys.exit(2)


# Config functions #########################################################

def get_config_data(install_dir):
    config_cmd = ['/usr/bin/env', 'php', '%s/config_to_json.php' % install_dir]
    exit_code, values = command_runner(config_cmd, timeout=10)
    if exit_code == 0:
        return values
    else:
        logger.error("ERROR: Could not execute: %s" % config_cmd)
        logger.error("exit code: {0}. Output:\n{1}".format(exit_code, values))
        sys.exit(2)

# Database functions #######################################################


def db_open(db_socket, db_server, db_port, db_username, db_password, db_dbname):
    try:
        options = dict(host=db_server, port=int(db_port), user=db_username, passwd=db_password, db=db_dbname)

        if db_socket:
            options['unix_socket'] = db_socket

        return MySQLdb.connect(**options)
    except Exception as exc:
        logger.error('ERROR: Could not connect to MySQL database!')
        logger.error('{0}'.format(exc))
        sys.exit(2)
