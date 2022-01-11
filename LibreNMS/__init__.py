import json
import logging
import os
import sys
import tempfile
import threading
import timeit
from collections import deque
from logging.handlers import RotatingFileHandler
from math import ceil
from queue import Queue
from time import time

from .command_runner import command_runner
from .queuemanager import (
    QueueManager,
    TimedQueueManager,
    BillingQueueManager,
    PingQueueManager,
    ServicesQueueManager,
    AlertQueueManager,
    PollerQueueManager,
    DiscoveryQueueManager,
)
from .service import Service, ServiceConfig

# Hard limit script execution time so we don't get to "hang"
DEFAULT_SCRIPT_TIMEOUT = 3600
MAX_LOGFILE_SIZE = (1024 ** 2) * 10  # 10 Megabytes max log files

logger = logging.getLogger(__name__)

# Logging functions ########################################################
# Original logger functions from ofunctions.logger_utils package

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
            log_file,
            mode="a",
            encoding="utf-8",
            maxBytes=MAX_LOGFILE_SIZE,
            backupCount=3,
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
                maxBytes=MAX_LOGFILE_SIZE,
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
        logger.error("File '%s' is not readable" % file)
        logger.debug("Traceback:", exc_info=True)
        sys.exit(2)


# Config functions #########################################################


def get_config_data(base_dir):
    env_path = os.path.join(base_dir, ".env")
    check_for_file(env_path)

    try:
        import dotenv

        logger.info("Attempting to load .env from '%s'", env_path)
        dotenv.load_dotenv(dotenv_path=env_path, verbose=True)

        if not os.getenv("NODE_ID"):
            logger.critical(".env does not contain a valid NODE_ID setting.")

    except (ImportError, ModuleNotFoundError) as exc:
        logger.critical(
            'Could not import "%s" - Please check that the poller user can read the file, and python-dotenv/python3-dotenv is installed\nAdditional info: %s'
            % (env_path, exc)
        )
        logger.debug("Traceback:", exc_info=True)

    config_cmd = ["/usr/bin/env", "php", "%s/config_to_json.php" % base_dir]
    try:
        exit_code, output = command_runner(config_cmd, timeout=300, stderr=False)
        if exit_code != 0:
            logger.critical("Error in config fetching process: %s" % exit_code)
        return json.loads(output)
    except Exception as exc:
        logger.critical("ERROR: Could not execute command [%s]: %s" % (config_cmd, exc))
        logger.debug("Traceback:", exc_info=True)
        return None


def normalize_wait(seconds):
    return ceil(seconds - (time() % seconds))


def call_script(script, args=()):
    """
    Run a LibreNMS script.  Captures all output returns exit code.
    Blocks parent signals (like SIGINT and SIGTERM).
    Kills script if it takes too long
    :param script: the name of the executable relative to the base directory
    :param args: a tuple of arguments to send to the command
    :returns the output of the command
    """
    if script.endswith(".php"):
        # save calling the sh process
        base = ("/usr/bin/env", "php")
    else:
        base = ()

    base_dir = os.path.realpath(os.path.dirname(__file__) + "/..")
    cmd = base + ("{}/{}".format(base_dir, script),) + tuple(map(str, args))
    logger.debug("Running {}".format(cmd))
    # preexec_fn=os.setsid here keeps process signals from propagating (close_fds=True is default)
    return command_runner(
        cmd, preexec_fn=os.setsid, close_fds=True, timeout=DEFAULT_SCRIPT_TIMEOUT
    )


class DB:
    def __init__(self, config, auto_connect=True):
        """
        Simple DB wrapper
        :param config: The poller config object
        """
        self.config = config
        self._db = {}

        if auto_connect:
            self.connect()

    def connect(self):
        try:
            import pymysql

            pymysql.install_as_MySQLdb()
            logger.debug("Using pure python SQL client")
        except ImportError:
            logger.debug("Using other SQL client")

        try:
            import MySQLdb
        except ImportError:
            logger.critical("ERROR: missing a mysql python module")
            logger.critical(
                "Install either 'PyMySQL' or 'mysqlclient' from your OS software repository or from PyPI"
            )
            raise

        try:
            args = {
                "host": self.config.db_host,
                "port": self.config.db_port,
                "user": self.config.db_user,
                "passwd": self.config.db_pass,
                "db": self.config.db_name,
            }
            if self.config.db_socket:
                args["unix_socket"] = self.config.db_socket

            conn = MySQLdb.connect(**args)
            conn.autocommit(True)
            conn.ping(True)
            self._db[threading.get_ident()] = conn
        except Exception as e:
            logger.critical("ERROR: Could not connect to MySQL database! {}".format(e))
            raise

    def db_conn(self):
        """
        Refers to a database connection via thread identifier
        :return: database connection handle
        """

        # Does a connection exist for this thread
        if threading.get_ident() not in self._db.keys():
            self.connect()

        return self._db[threading.get_ident()]

    def query(self, query, args=None):
        """
        Open a cursor, fetch the query with args, close the cursor and return it.
        :rtype: MySQLdb.Cursor
        :param query:
        :param args:
        :return: the cursor with results
        """
        try:
            cursor = self.db_conn().cursor()
            cursor.execute(query, args)
            cursor.close()
            return cursor
        except Exception as e:
            logger.critical("DB Connection exception {}".format(e))
            self.close()
            raise

    def close(self):
        """
        Close the connection owned by this thread.
        """
        conn = self._db.pop(threading.get_ident(), None)
        if conn:
            conn.close()


class RecurringTimer:
    def __init__(self, duration, target, thread_name=None):
        self.duration = duration
        self.target = target
        self._timer_thread = None
        self._thread_name = thread_name
        self._event = threading.Event()

    def _loop(self):
        while not self._event.is_set():
            self._event.wait(normalize_wait(self.duration))
            if not self._event.is_set():
                self.target()

    def start(self):
        self._timer_thread = threading.Thread(target=self._loop)
        if self._thread_name:
            self._timer_thread.name = self._thread_name
        self._event.clear()
        self._timer_thread.start()

    def stop(self):
        self._event.set()


class Lock:
    """Base lock class this is not thread safe"""

    def __init__(self):
        self._locks = {}  # store a tuple (owner, expiration)

    def lock(self, name, owner, expiration, allow_owner_relock=False):
        """
        Obtain the named lock.
        :param allow_owner_relock:
        :param name: str the name of the lock
        :param owner: str a unique name for the locking node
        :param expiration: int in seconds
        """
        if (
            (name not in self._locks)
            or (  # lock doesn't exist
                allow_owner_relock and self._locks.get(name, [None])[0] == owner
            )
            or time() > self._locks[name][1]  # owner has permission  # lock has expired
        ):
            self._locks[name] = (owner, expiration + time())
            return self._locks[name][0] == owner

        return False

    def unlock(self, name, owner):
        """
        Release the named lock.
        :param name: str the name of the lock
        :param owner: str a unique name for the locking node
        """
        if (name in self._locks) and self._locks[name][0] == owner:
            self._locks.pop(name, None)
            return True
        return False

    def check_lock(self, name):
        lock = self._locks.get(name, None)
        if lock:
            return lock[1] > time()
        return False

    def print_locks(self):
        logger.debug(self._locks)


class ThreadingLock(Lock):
    """A subclass of Lock that uses thread-safe locking"""

    def __init__(self):
        Lock.__init__(self)
        self._lock = threading.Lock()

    def lock(self, name, owner, expiration, allow_owner_relock=False):
        """
        Obtain the named lock.
        :param allow_owner_relock:
        :param name: str the name of the lock
        :param owner: str a unique name for the locking node
        :param expiration: int in seconds
        """
        with self._lock:
            return Lock.lock(self, name, owner, expiration, allow_owner_relock)

    def unlock(self, name, owner):
        """
        Release the named lock.
        :param name: str the name of the lock
        :param owner: str a unique name for the locking node
        """
        with self._lock:
            return Lock.unlock(self, name, owner)

    def check_lock(self, name):
        return Lock.check_lock(self, name)

    def print_locks(self):
        Lock.print_locks(self)


class RedisLock(Lock):
    def __init__(self, namespace="lock", **redis_kwargs):
        import redis
        from redis.sentinel import Sentinel

        redis_kwargs["decode_responses"] = True
        if redis_kwargs.get("sentinel") and redis_kwargs.get("sentinel_service"):
            sentinels = [
                tuple(l.split(":")) for l in redis_kwargs.pop("sentinel").split(",")
            ]
            sentinel_service = redis_kwargs.pop("sentinel_service")
            kwargs = {
                k: v
                for k, v in redis_kwargs.items()
                if k in ["decode_responses", "password", "db", "socket_timeout"]
            }
            self._redis = Sentinel(sentinels, **kwargs).master_for(sentinel_service)
        else:
            kwargs = {k: v for k, v in redis_kwargs.items() if "sentinel" not in k}
            self._redis = redis.Redis(**kwargs)
        self._redis.ping()
        self._namespace = namespace
        logger.debug(
            "Created redis lock manager with socket_timeout of {}s".format(
                redis_kwargs["socket_timeout"]
            )
        )

    def __key(self, name):
        return "{}:{}".format(self._namespace, name)

    def lock(self, name, owner, expiration=1, allow_owner_relock=False):
        """
        Obtain the named lock.
        :param allow_owner_relock: bool
        :param name: str the name of the lock
        :param owner: str a unique name for the locking node
        :param expiration: int in seconds, 0 expiration means forever
        """
        import redis

        try:
            if int(expiration) < 1:
                expiration = 1

            key = self.__key(name)
            non_existing = not (allow_owner_relock and self._redis.get(key) == owner)
            return self._redis.set(key, owner, ex=int(expiration), nx=non_existing)
        except redis.exceptions.ResponseError as e:
            logger.critical(
                "Unable to obtain lock, local state: name: %s, owner: %s, expiration: %s, allow_owner_relock: %s",
                name,
                owner,
                expiration,
                allow_owner_relock,
            )

    def unlock(self, name, owner):
        """
        Release the named lock.
        :param name: str the name of the lock
        :param owner: str a unique name for the locking node
        """
        key = self.__key(name)
        if self._redis.get(key) == owner:
            self._redis.delete(key)
            return True
        return False

    def check_lock(self, name):
        return self._redis.get(self.__key(name)) is not None

    def print_locks(self):
        keys = self._redis.keys(self.__key("*"))
        for key in keys:
            print(
                "{} locked by {}, expires in {} seconds".format(
                    key, self._redis.get(key), self._redis.ttl(key)
                )
            )


class RedisUniqueQueue(object):
    def __init__(self, name, namespace="queue", **redis_kwargs):
        import redis
        from redis.sentinel import Sentinel

        redis_kwargs["decode_responses"] = True
        if redis_kwargs.get("sentinel") and redis_kwargs.get("sentinel_service"):
            sentinels = [
                tuple(l.split(":")) for l in redis_kwargs.pop("sentinel").split(",")
            ]
            sentinel_service = redis_kwargs.pop("sentinel_service")
            kwargs = {
                k: v
                for k, v in redis_kwargs.items()
                if k in ["decode_responses", "password", "db", "socket_timeout"]
            }
            self._redis = Sentinel(sentinels, **kwargs).master_for(sentinel_service)
        else:
            kwargs = {k: v for k, v in redis_kwargs.items() if "sentinel" not in k}
            self._redis = redis.Redis(**kwargs)
        self._redis.ping()
        self.key = "{}:{}".format(namespace, name)
        logger.debug(
            "Created redis queue with socket_timeout of {}s".format(
                redis_kwargs["socket_timeout"]
            )
        )

        # clean up from previous implementations
        if self._redis.type(self.key) != "zset":
            self._redis.delete(self.key)

    def qsize(self):
        return self._redis.zcount(self.key, "-inf", "+inf")

    def empty(self):
        return self.qsize() == 0

    def put(self, item):
        self._redis.zadd(self.key, {item: time()}, nx=True)

    def get(self, block=True, timeout=None):
        try:
            if block:
                item = self._redis.bzpopmin(self.key, timeout=timeout)
            else:
                item = self._redis.zpopmin(self.key)
        # Unfortunately we cannot use _redis.exceptions.ResponseError Exception here
        # Since it would trigger another exception in queuemanager
        except Exception as e:
            logger.critical(
                "BZPOPMIN/ZPOPMIN command failed: {}\nNote that redis >= 5.0 is required.".format(
                    e
                )
            )
            raise

        if item:
            item = item[1]
        return item

    def get_nowait(self):
        return self.get(False)


class UniqueQueue(Queue):
    def _init(self, maxsize):
        self.queue = deque()
        self.setqueue = set()

    def _put(self, item):
        if item not in self.setqueue:
            self.setqueue.add(item)
            self.queue.append(item)

    def _get(self):
        item = self.queue.popleft()
        self.setqueue.remove(item)
        return item


class PerformanceCounter(object):
    """
    This is a simple counter to record execution time and number of jobs. It's unique to each
    poller instance, so does not need to be globally syncronised, just locally.
    """

    def __init__(self):
        self._count = 0
        self._jobs = 0
        self._lock = threading.Lock()

    def add(self, n):
        """
        Add n to the counter and increment the number of jobs by 1
        :param n: Number to increment by
        """
        with self._lock:
            self._count += n
            self._jobs += 1

    def split(self, precise=False):
        """
        Return the current counter value and keep going
        :param precise: Whether floating point precision is desired
        :return: ((INT or FLOAT), INT)
        """
        return (self._count if precise else int(self._count)), self._jobs

    def reset(self, precise=False):
        """
        Return the current counter value and then zero it.
        :param precise: Whether floating point precision is desired
        :return: ((INT or FLOAT), INT)
        """
        with self._lock:
            c = self._count
            j = self._jobs
            self._count = 0
            self._jobs = 0

            return (c if precise else int(c)), j


class TimeitContext(object):
    """
    Wrapper around timeit to allow the timing of larger blocks of code by wrapping them in "with"
    """

    def __init__(self):
        self._t = timeit.default_timer()

    def __enter__(self):
        return self

    def __exit__(self, *args):
        del self._t

    def delta(self):
        """
        Calculate the elapsed time since the context was initialised
        :return: FLOAT
        """
        if not self._t:
            raise ArithmeticError("Timer has not been started, cannot return delta")

        return timeit.default_timer() - self._t

    @classmethod
    def start(cls):
        """
        Factory method for TimeitContext
        :param cls:
        :return: TimeitContext
        """
        return cls()
