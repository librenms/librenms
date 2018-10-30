import LibreNMS

import json
import logging
import os
import subprocess
import threading
import sys
import time
import timeit

from datetime import timedelta
from logging import debug, info, warning, error, critical, exception
from platform import python_version
from time import sleep
from socket import gethostname
from signal import signal, SIGTERM
from uuid import uuid1


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


class ServiceConfig:
    def __init__(self):
        """
        Stores all of the configuration variables for the LibreNMS service in a common object
        Starts with defaults, but can be populated with variables from config.php by calling populate()
        """
        self._uuid = str(uuid1())
        self.set_name(gethostname())

    def set_name(self, name):
        if name:
            self.name = name.strip()
            self.unique_name = "{}-{}".format(self.name, self._uuid)

    class PollerConfig:
        def __init__(self, workers, frequency, calculate=None):
            self.workers = workers
            self.frequency = frequency
            self.calculate = calculate

    # config variables with defaults
    BASE_DIR = os.path.abspath(os.path.join(os.path.dirname(os.path.realpath(__file__)), os.pardir))

    node_id = None
    name = None
    unique_name = None
    single_instance = True
    distributed = False
    group = 0

    debug = False
    log_level = 20

    alerting = PollerConfig(1, 60)
    poller = PollerConfig(24, 300)
    services = PollerConfig(8, 300)
    discovery = PollerConfig(16, 21600)
    billing = PollerConfig(2, 300, 60)
    down_retry = 60
    update_frequency = 86400

    master_resolution = 1
    master_timeout = 10

    redis_host = 'localhost'
    redis_port = 6379
    redis_db = 0
    redis_pass = None
    redis_socket = None

    db_host = 'localhost'
    db_port = 0
    db_socket = None
    db_user = 'librenms'
    db_pass = ''
    db_name = 'librenms'

    def populate(self):
        config = self._get_config_data()

        # populate config variables
        self.node_id = os.getenv('NODE_ID')
        self.set_name(config.get('distributed_poller_name', None))
        self.distributed = config.get('distributed_poller', ServiceConfig.distributed)
        self.group = ServiceConfig.parse_group(config.get('distributed_poller_group', ServiceConfig.group))

        # backward compatible options
        self.poller.workers = config.get('poller_service_workers', ServiceConfig.poller.workers)
        self.poller.frequency = config.get('poller_service_poll_frequency', ServiceConfig.poller.frequency)
        self.discovery.frequency = config.get('poller_service_discover_frequency', ServiceConfig.discovery.frequency)
        self.down_retry = config.get('poller_service_down_retry', ServiceConfig.down_retry)
        self.log_level = config.get('poller_service_loglevel', ServiceConfig.log_level)

        # new options
        self.poller.workers = config.get('service_poller_workers', ServiceConfig.poller.workers)
        self.poller.frequency = config.get('service_poller_frequency', ServiceConfig.poller.frequency)
        self.services.workers = config.get('service_services_workers', ServiceConfig.services.workers)
        self.services.frequency = config.get('service_services_frequency', ServiceConfig.services.frequency)
        self.discovery.workers = config.get('service_discovery_workers', ServiceConfig.discovery.workers)
        self.discovery.frequency = config.get('service_discovery_frequency', ServiceConfig.discovery.frequency)
        self.billing.frequency = config.get('service_billing_frequency', ServiceConfig.billing.frequency)
        self.billing.calculate = config.get('service_billing_calculate_frequency', ServiceConfig.billing.calculate)
        self.down_retry = config.get('service_poller_down_retry', ServiceConfig.down_retry)
        self.log_level = config.get('service_loglevel', ServiceConfig.log_level)
        self.update_frequency = config.get('service_update_frequency', ServiceConfig.update_frequency)

        self.redis_host = os.getenv('REDIS_HOST', config.get('redis_host', ServiceConfig.redis_host))
        self.redis_db = os.getenv('REDIS_DB', config.get('redis_db', ServiceConfig.redis_db))
        self.redis_pass = os.getenv('REDIS_PASSWORD', config.get('redis_pass', ServiceConfig.redis_pass))
        self.redis_port = int(os.getenv('REDIS_PORT', config.get('redis_port', ServiceConfig.redis_port)))
        self.redis_socket = os.getenv('REDIS_SOCKET', config.get('redis_socket', ServiceConfig.redis_socket))

        self.db_host = os.getenv('DB_HOST', config.get('db_host', ServiceConfig.db_host))
        self.db_name = os.getenv('DB_DATABASE', config.get('db_name', ServiceConfig.db_name))
        self.db_pass = os.getenv('DB_PASSWORD', config.get('db_pass', ServiceConfig.db_pass))
        self.db_port = int(os.getenv('DB_PORT', config.get('db_port', ServiceConfig.db_port)))
        self.db_socket = os.getenv('DB_SOCKET', config.get('db_socket', ServiceConfig.db_socket))
        self.db_user = os.getenv('DB_USERNAME', config.get('db_user', ServiceConfig.db_user))

        # set convenient debug variable
        self.debug = logging.getLogger().isEnabledFor(logging.DEBUG)

        if not self.debug and self.log_level:
            try:
                logging.getLogger().setLevel(self.log_level)
            except ValueError:
                error("Unknown log level {}, must be one of 'DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'".format(self.log_level))
                logging.getLogger().setLevel(logging.INFO)

    def _get_config_data(self):
        try:
            import dotenv
            env_path =  "{}/.env".format(self.BASE_DIR)
            info("Attempting to load .env from '%s'", env_path)
            dotenv.load_dotenv(dotenv_path=env_path, verbose=True)

            if not os.getenv('NODE_ID'):
                raise ImportError(".env does not contain a valid NODE_ID setting.")

        except ImportError as e:
            exception("Could not import .env - check that the poller user can read the file, and that composer install has been run recently")
            sys.exit(3)

        config_cmd = ['/usr/bin/env', 'php', '{}/config_to_json.php'.format(self.BASE_DIR), '2>&1']
        try:
            return json.loads(subprocess.check_output(config_cmd).decode())
        except subprocess.CalledProcessError as e:
            error("ERROR: Could not load or parse configuration! {}: {}"
                  .format(subprocess.list2cmdline(e.cmd), e.output.decode()))

    @staticmethod
    def parse_group(g):
        if g is None:
            return [0]
        elif type(g) is int:
            return [g]
        elif type(g) is str:
            try:
                return [int(x) for x in set(g.split(','))]
            except ValueError:
                pass

        error("Could not parse group string, defaulting to 0")
        return [0]


class Service:
    config = ServiceConfig()
    _fp = False
    _started = False
    alerting_manager = None
    poller_manager = None
    discovery_manager = None
    services_manager = None
    billing_manager = None
    last_poll = {}
    terminate_flag = False

    def __init__(self):
        self.config.populate()
        threading.current_thread().name = self.config.name  # rename main thread

        self.attach_signals()

        # init database connections different ones for different threads
        self._db = LibreNMS.DB(self.config)  # main
        self._services_db = LibreNMS.DB(self.config)  # services dispatch
        self._discovery_db = LibreNMS.DB(self.config)  # discovery dispatch

        self._lm = self.create_lock_manager()
        self.daily_timer = LibreNMS.RecurringTimer(self.config.update_frequency, self.run_maintenance, 'maintenance')
        self.stats_timer = LibreNMS.RecurringTimer(self.config.poller.frequency, self.log_performance_stats, 'performance')
        self.is_master = False

        self.performance_stats = {'poller': PerformanceCounter(), 'discovery': PerformanceCounter(), 'services': PerformanceCounter()}

    def attach_signals(self):
        info("Attaching signal handlers on thread %s", threading.current_thread().name)
        signal(SIGTERM, self.terminate)  # capture sigterm and exit gracefully

    def start(self):
        debug("Performing startup checks...")

        if self.config.single_instance:
            self.check_single_instance()  # don't allow more than one service at a time

        if self._started:
            raise RuntimeWarning("Not allowed to start Poller twice")
        self._started = True

        debug("Starting up queue managers...")

        # initialize and start the worker pools
        self.poller_manager = LibreNMS.QueueManager(self.config, 'poller', self.poll_device)
        self.alerting_manager = LibreNMS.TimedQueueManager(self.config, 'alerting', self.poll_alerting,
                                                           self.dispatch_alerting)
        self.services_manager = LibreNMS.TimedQueueManager(self.config, 'services', self.poll_services,
                                                           self.dispatch_services)
        self.discovery_manager = LibreNMS.TimedQueueManager(self.config, 'discovery', self.discover_device,
                                                            self.dispatch_discovery)
        self.billing_manager = LibreNMS.BillingQueueManager(self.config, self.poll_billing,
                                                            self.dispatch_poll_billing, self.dispatch_calculate_billing)

        self.daily_timer.start()
        self.stats_timer.start()

        info("LibreNMS Service: {} started!".format(self.config.unique_name))
        info("Poller group {}. Using Python {} and {} locks and queues"
             .format('0 (default)' if self.config.group == [0] else self.config.group, python_version(),
                     'redis' if isinstance(self._lm, LibreNMS.RedisLock) else 'internal'))
        info("Maintenance tasks will be run every {}".format(timedelta(seconds=self.config.update_frequency)))

        # Main dispatcher loop
        try:
            while not self.terminate_flag:
                master_lock = self._lm.lock('dispatch.master', self.config.unique_name, self.config.master_timeout, True)
                if master_lock:
                    if not self.is_master:
                        info("{} is now the master dispatcher".format(self.config.name))
                        self.is_master = True
                        self.start_dispatch_timers()

                    devices = self.fetch_immediate_device_list()
                    for device in devices:
                        device_id = device[0]
                        group = device[1]

                        if device[2]:  # polling
                            self.dispatch_immediate_polling(device_id, group)

                        if device[3]:  # discovery
                            self.dispatch_immediate_discovery(device_id, group)
                else:
                    if self.is_master:
                        info("{} is no longer the master dispatcher".format(self.config.name))
                        self.stop_dispatch_timers()
                        self.is_master = False  # no longer master
                sleep(self.config.master_resolution)
        except KeyboardInterrupt:
            pass

        info("Dispatch loop terminated")
        self.shutdown()

    # ------------ Discovery ------------
    def dispatch_immediate_discovery(self, device_id, group):
        if self.discovery_manager.get_queue(group).empty() and not self.discovery_is_locked(device_id):
            self.discovery_manager.post_work(device_id, group)

    def dispatch_discovery(self):
        devices = self.fetch_device_list()
        for device in devices:
            self.discovery_manager.post_work(device[0], device[1])

    def discover_device(self, device_id):
        if self.lock_discovery(device_id):
            try:
                with TimeitContext.start() as t:
                    info("Discovering device {}".format(device_id))
                    self.call_script('discovery.php', ('-h', device_id))
                    info('Discovery complete {}'.format(device_id))
                    self.report_execution_time(t.delta(), 'discovery')
            except subprocess.CalledProcessError as e:
                if e.returncode == 5:
                    info("Device {} is down, cannot discover, waiting {}s for retry"
                         .format(device_id, self.config.down_retry))
                    self.lock_discovery(device_id, True)
                else:
                    self.unlock_discovery(device_id)
            else:
                self.unlock_discovery(device_id)

    # ------------ Alerting ------------
    def dispatch_alerting(self):
        self.alerting_manager.post_work('alerts', 0)

    def poll_alerting(self, _=None):
        try:
            info("Checking alerts")
            self.call_script('alerts.php')
        except subprocess.CalledProcessError as e:
            if e.returncode == 1:
                warning("There was an error issuing alerts: {}".format(e.output))
            else:
                raise

    # ------------ Services ------------
    def dispatch_services(self):
        devices = self.fetch_services_device_list()
        for device in devices:
            self.services_manager.post_work(device[0], device[1])

    def poll_services(self, device_id):
        if self.lock_services(device_id):
            try:
                with TimeitContext.start() as t:
                    info("Checking services on device {}".format(device_id))
                    self.call_script('check-services.php', ('-h', device_id))
                    info('Services complete {}'.format(device_id))
                    self.report_execution_time(t.delta(), 'services')
            except subprocess.CalledProcessError as e:
                if e.returncode == 5:
                    info("Device {} is down, cannot poll service, waiting {}s for retry"
                         .format(device_id, self.config.down_retry))
                    self.lock_services(device_id, True)
                else:
                    self.unlock_services(device_id)
            else:
                self.unlock_services(device_id)

    # ------------ Billing ------------
    def dispatch_calculate_billing(self):
        self.billing_manager.post_work('calculate', 0)

    def dispatch_poll_billing(self):
        self.billing_manager.post_work('poll', 0)

    def poll_billing(self, run_type):
        if run_type == 'poll':
            info("Polling billing")
            self.call_script('poll-billing.php')
            info("Polling billing complete")
        else:  # run_type == 'calculate'
            info("Calculating billing")
            self.call_script('billing-calculate.php')
            info("Calculating billing complete")

    # ------------ Polling ------------
    def dispatch_immediate_polling(self, device_id, group):
        if self.poller_manager.get_queue(group).empty() and not self.polling_is_locked(device_id):
            self.poller_manager.post_work(device_id, group)

            if self.config.debug:
                cur_time = time.time()
                elapsed = cur_time - self.last_poll.get(device_id, cur_time)
                self.last_poll[device_id] = time.time()
                # arbitrary limit to reduce spam
                if elapsed > (self.config.poller.frequency - self.config.master_resolution):
                    debug("Dispatching polling for device {}, time since last poll {:.2f}s"
                          .format(device_id, elapsed))

    def poll_device(self, device_id):
        if self.lock_polling(device_id):
            info('Polling device {}'.format(device_id))

            try:
                with TimeitContext.start() as t:
                    self.call_script('poller.php', ('-h', device_id))
                    self.report_execution_time(t.delta(), 'poller')
            except subprocess.CalledProcessError as e:
                if e.returncode == 6:
                    warning('Polling device {} unreachable, waiting {}s for retry'.format(device_id, self.config.down_retry))
                    # re-lock to set retry timer
                    self.lock_polling(device_id, True)
                else:
                    error('Polling device {} failed! {}'.format(device_id, e))
                    self.unlock_polling(device_id)
            else:
                info('Polling complete {}'.format(device_id))
                # self.polling_unlock(device_id)
        else:
            debug('Tried to poll {}, but it is locked'.format(device_id))

    def fetch_services_device_list(self):
        return self._services_db.query("SELECT DISTINCT(`device_id`), `poller_group` FROM `services`"
                                       " LEFT JOIN `devices` USING (`device_id`) WHERE `disabled`=0")

    def fetch_device_list(self):
        return self._discovery_db.query("SELECT `device_id`, `poller_group` FROM `devices` WHERE `disabled`=0")

    def fetch_immediate_device_list(self):
        poller_find_time = self.config.poller.frequency - 1
        discovery_find_time = self.config.discovery.frequency - 1

        return self._db.query('''SELECT `device_id`,
              `poller_group`,
              COALESCE(`last_polled` <= DATE_ADD(DATE_ADD(NOW(), INTERVAL -%s SECOND), INTERVAL `last_polled_timetaken` SECOND), 1) AS `poll`,
              COALESCE(`last_discovered` <= DATE_ADD(DATE_ADD(NOW(), INTERVAL -%s SECOND), INTERVAL `last_discovered_timetaken` SECOND), 1) AS `discover`
            FROM `devices`
            WHERE `disabled` = 0 AND (
                `last_polled` IS NULL OR
                `last_discovered` IS NULL OR
                `last_polled` <= DATE_ADD(DATE_ADD(NOW(), INTERVAL -%s SECOND), INTERVAL `last_polled_timetaken` SECOND) OR
                `last_discovered` <= DATE_ADD(DATE_ADD(NOW(), INTERVAL -%s SECOND), INTERVAL `last_discovered_timetaken` SECOND)
            )
            ORDER BY `last_polled_timetaken` DESC''', (poller_find_time, discovery_find_time, poller_find_time, discovery_find_time))

    def run_maintenance(self):
        """
        Runs update and cleanup tasks by calling daily.sh.  Reloads the python script after the update.
        Sets a schema-update lock so no distributed pollers will update until the schema has been updated.
        """
        attempt = 0
        wait = 5
        max_runtime = 86100
        max_tries = int(max_runtime / wait)
        info("Waiting for schema lock")
        while not self._lm.lock('schema-update', self.config.unique_name, max_runtime):
            attempt += 1
            if attempt >= max_tries:  # don't get stuck indefinitely
                warning('Reached max wait for other pollers to update, updating now')
                break
            sleep(wait)

        info("Running maintenance tasks")
        output = self.call_script('daily.sh')
        info("Maintenance tasks complete\n{}".format(output))

        self.restart()

    # Lock Helpers #
    def lock_discovery(self, device_id, retry=False):
        lock_name = self.gen_lock_name('discovery', device_id)
        timeout = self.config.down_retry if retry else LibreNMS.normalize_wait(self.config.discovery.frequency)
        return self._lm.lock(lock_name, self.gen_lock_owner(), timeout, retry)

    def unlock_discovery(self, device_id):
        lock_name = self.gen_lock_name('discovery', device_id)
        return self._lm.unlock(lock_name, self.gen_lock_owner())

    def discovery_is_locked(self, device_id):
        lock_name = self.gen_lock_name('discovery', device_id)
        return self._lm.check_lock(lock_name)

    def lock_polling(self, device_id, retry=False):
        lock_name = self.gen_lock_name('polling', device_id)
        timeout = self.config.down_retry if retry else self.config.poller.frequency
        return self._lm.lock(lock_name, self.gen_lock_owner(), timeout, retry)

    def unlock_polling(self, device_id):
        lock_name = self.gen_lock_name('polling', device_id)
        return self._lm.unlock(lock_name, self.gen_lock_owner())

    def polling_is_locked(self, device_id):
        lock_name = self.gen_lock_name('polling', device_id)
        return self._lm.check_lock(lock_name)

    def lock_services(self, device_id, retry=False):
        lock_name = self.gen_lock_name('services', device_id)
        timeout = self.config.down_retry if retry else self.config.services.frequency
        return self._lm.lock(lock_name, self.gen_lock_owner(), timeout, retry)

    def unlock_services(self, device_id):
        lock_name = self.gen_lock_name('services', device_id)
        return self._lm.unlock(lock_name, self.gen_lock_owner())

    def services_is_locked(self, device_id):
        lock_name = self.gen_lock_name('services', device_id)
        return self._lm.check_lock(lock_name)

    @staticmethod
    def gen_lock_name(lock_class, device_id):
        return '{}.device.{}'.format(lock_class, device_id)

    def gen_lock_owner(self):
        return "{}-{}".format(self.config.unique_name, threading.current_thread().name)

    def call_script(self, script, args=()):
        """
        Run a LibreNMS script.  Captures all output and throws an exception if a non-zero
        status is returned.  Blocks parent signals (like SIGINT and SIGTERM).
        :param script: the name of the executable relative to the base directory
        :param args: a tuple of arguments to send to the command
        :returns the output of the command
        """
        if script.endswith('.php'):
            # save calling the sh process
            base = ('/usr/bin/env', 'php')
        else:
            base = ()

        cmd = base + ("{}/{}".format(self.config.BASE_DIR, script),) + tuple(map(str, args))
        # preexec_fn=os.setsid here keeps process signals from propagating
        return subprocess.check_output(cmd, stderr=subprocess.STDOUT, preexec_fn=os.setsid, close_fds=True).decode()

    def create_lock_manager(self):
        """
        Create a new LockManager.  Tries to create a Redis LockManager, but falls
        back to python's internal threading lock implementation.
        Exits if distributing poller is enabled and a Redis LockManager cannot be created.
        :return: Instance of LockManager
        """
        try:
            return LibreNMS.RedisLock(namespace='librenms.lock',
                                      host=self.config.redis_host,
                                      port=self.config.redis_port,
                                      db=self.config.redis_db,
                                      password=self.config.redis_pass,
                                      unix_socket_path=self.config.redis_socket)
        except ImportError:
            if self.config.distributed:
                critical("ERROR: Redis connection required for distributed polling")
                critical("Please install redis-py, either through your os software repository or from PyPI")
                sys.exit(2)
        except Exception as e:
            if self.config.distributed:
                critical("ERROR: Redis connection required for distributed polling")
                critical("Could not connect to Redis. {}".format(e))
                sys.exit(2)

        return LibreNMS.ThreadingLock()

    def restart(self):
        """
        Stop then recreate this entire process by re-calling the original script.
        Has the effect of reloading the python files from disk.
        """
        if sys.version_info < (3, 4, 0):
            warning("Skipping restart as running under an incompatible interpreter")
            warning("Please restart manually")
            return

        info('Restarting service... ')
        self._stop_managers_and_wait()
        self._lm.unlock('dispatch.master', self.config.unique_name)

        python = sys.executable
        os.execl(python, python, *sys.argv)

    def terminate(self, _unused=None, _=None):
        """
        Handle a set the terminate flag to begin a clean shutdown
        :param _unused:
        :param _:
        """
        info("Received SIGTERM on thead %s, handling", threading.current_thread().name)
        self.terminate_flag = True

    def shutdown(self, _unused=None, _=None):
        """
        Stop and exit, waiting for all child processes to exit.
        :param _unused:
        :param _:
        """
        info('Shutting down, waiting for running jobs to complete...')

        self.stop_dispatch_timers()
        self._lm.unlock('dispatch.master', self.config.unique_name)

        self.daily_timer.stop()
        self.stats_timer.stop()

        self._stop_managers_and_wait()

        # try to release master lock
        info('Shutdown of %s/%s complete', os.getpid(), threading.current_thread().name)
        sys.exit(0)

    def start_dispatch_timers(self):
        """
        Start all dispatch timers and begin pushing events into queues.
        This should only be started when we are the master dispatcher.
        """
        self.alerting_manager.start_dispatch()
        self.billing_manager.start_dispatch()
        self.services_manager.start_dispatch()
        self.discovery_manager.start_dispatch()

    def stop_dispatch_timers(self):
        """
        Stop all dispatch timers, this should be called when we are no longer the master dispatcher.
        """
        self.alerting_manager.stop_dispatch()
        self.billing_manager.stop_dispatch()
        self.services_manager.stop_dispatch()
        self.discovery_manager.stop_dispatch()

    def _stop_managers_and_wait(self):
        """
        Stop all QueueManagers, and wait for their processing threads to complete.
        We send the stop signal to all QueueManagers first, then wait for them to finish.
        """
        self.discovery_manager.stop()
        self.poller_manager.stop()
        self.services_manager.stop()
        self.billing_manager.stop()

        self.discovery_manager.stop_and_wait()
        self.poller_manager.stop_and_wait()
        self.services_manager.stop_and_wait()
        self.billing_manager.stop_and_wait()

    def check_single_instance(self):
        """
        Check that there is only one instance of the service running on this computer.
        We do this be creating a file in the base directory (.lock.service) if it doesn't exist and
        obtaining an exclusive lock on that file.
        """
        lock_file = "{}/{}".format(self.config.BASE_DIR, '.lock.service')

        import fcntl
        self._fp = open(lock_file, 'w')  # keep a reference so the file handle isn't garbage collected
        self._fp.flush()
        try:
            fcntl.lockf(self._fp, fcntl.LOCK_EX | fcntl.LOCK_NB)
        except IOError:
            warning("Another instance is already running, quitting.")
            exit(2)

    def report_execution_time(self, time, activity):
        self.performance_stats[activity].add(time)

    def log_performance_stats(self):
        info("Counting up time spent polling")

        try:
            # Report on the poller instance as a whole
            self._db.query('INSERT INTO poller_cluster(node_id, poller_name, poller_version, poller_groups, last_report, master) '
                           'values("{0}", "{1}", "{2}", "{3}", NOW(), {4}) '
                           'ON DUPLICATE KEY UPDATE poller_version="{2}", poller_groups="{3}", last_report=NOW(), master={4}; '
                           .format(self.config.node_id, self.config.name, "librenms-service", ','.join(str(g) for g in self.config.group), 1 if self.is_master else 0))

            # Find our ID
            self._db.query('SELECT id INTO @parent_poller_id FROM poller_cluster WHERE node_id="{0}"; '.format(self.config.node_id))

            for worker_type, counter in self.performance_stats.items():
                worker_seconds, devices = counter.reset()

                # Record the queue state
                self._db.query('INSERT INTO poller_cluster_stats(parent_poller, poller_type, depth, devices, worker_seconds, workers, frequency) '
                               'values(@parent_poller_id, "{0}", {1}, {2}, {3}, {4}, {5}) '
                               'ON DUPLICATE KEY UPDATE depth={1}, devices={2}, worker_seconds={3}, workers={4}, frequency={5}; '
                               .format(worker_type,
                                       sum([getattr(self, ''.join([worker_type, '_manager'])).get_queue(group).qsize() for group in self.config.group]),
                                       devices,
                                       worker_seconds,
                                       getattr(self.config, worker_type).workers,
                                       getattr(self.config, worker_type).frequency)
                               )
        except Exception:
            exception("Unable to log performance statistics - is the database still online?")
