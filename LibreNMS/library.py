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
    import pymysql
    pymysql.install_as_MySQLdb()
except ImportError:
    pass

try:
    import MySQLdb
except ImportError as exc:
    print('ERROR: missing the mysql python module please run:')
    print('pip install -r requirements.txt')
    print('ERROR: %s' % exc)
    sys.exit(2)

logger = logging.getLogger(__name__)

# Logging functions ########################################################

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


# Generic functions ########################################################

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
    try:
        proc = subprocess.Popen(config_cmd, stdout=subprocess.PIPE, stdin=subprocess.PIPE)
    except Exception as e:
        print("ERROR: Could not execute: %s" % config_cmd)
        print(e)
        sys.exit(2)
    return proc.communicate()[0]

# Database functions #######################################################


def db_open(db_socket, db_server, db_port, db_username, db_password, db_dbname):
    try:
        if db_socket:
            database = MySQLdb.connect(host=db_server, unix_socket=db_socket, user=db_username, passwd=db_password,
                                       db=db_dbname)
        else:
            database = MySQLdb.connect(host=db_server, port=db_port, user=db_username, passwd=db_password, db=db_dbname)
        return database
    except Exception as dbexc:
        print('ERROR: Could not connect to MySQL database!')
        print('ERROR: %s' % dbexc)
        sys.exit(2)


class DB:
    conn = None

    def __init__(self, db_socket, db_server, db_port, db_username, db_password, db_dbname):
        self.db_socket = db_socket
        self.db_server = db_server
        self.db_port = db_port
        self.db_username = db_username
        self.db_password = db_password
        self.db_dbname = db_dbname
        self.in_use = threading.Lock()
        self.connect()

    def connect(self):
        self.in_use.acquire(True)
        while True:
            try:
                self.conn.close()
            except:
                pass

            try:
                if self.db_port == 0:
                    self.conn = MySQLdb.connect(host=self.db_server, user=self.db_username, passwd=self.db_password, db=self.db_dbname)
                else:
                    self.conn = MySQLdb.connect(host=self.db_server, port=self.db_port, user=self.db_username, passwd=self.db_password,
                                                db=self.db_dbname)
                break
            except (AttributeError, MySQLdb.OperationalError):
                logger.warning('WARNING: MySQL Error, reconnecting.')
                time.sleep(.5)

        self.conn.autocommit(True)
        self.conn.ping(True)
        self.in_use.release()

    def query(self, sql):
        self.in_use.acquire(True)
        while True:
            try:
                cursor = self.conn.cursor()
                cursor.execute(sql)
                ret = cursor.fetchall()
                cursor.close()
                self.in_use.release()
                return ret
            except (AttributeError, MySQLdb.OperationalError):
                logger.warning('WARNING: MySQL Operational Error during query, reconnecting.')
                self.in_use.release()
                self.connect()
            except (AttributeError, MySQLdb.ProgrammingError):
                logger.warning('WARNING: MySQL Programming Error during query, attempting query again.')
                cursor.close()
