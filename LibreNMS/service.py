import LibreNMS

import json
import logging
import os
import pymysql
import subprocess
import threading
import sys
import time

from datetime import timedelta
from logging import debug, info, warning, error, critical, exception
from platform import python_version
from time import sleep
from socket import gethostname
from signal import signal, SIGTERM
from uuid import uuid1


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
            self.enabled = True
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
    max_db_failures = 5

    alerting = PollerConfig(1, 60)
    poller = PollerConfig(24, 300)
    services = PollerConfig(8, 300)
    discovery = PollerConfig(16, 21600)
    billing = PollerConfig(2, 300, 60)
    ping = PollerConfig(1, 120)
    down_retry = 60
    update_enabled = True
    update_frequency = 86400

    master_resolution = 1
    master_timeout = 10

    redis_host = 'localhost'
    redis_port = 6379
    redis_db = 0
    redis_pass = None
    redis_socket = None
    redis_sentinel = None
    redis_sentinel_service = None

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
        self.poller.enabled = config.get('service_poller_enabled', True)  # unused
        self.poller.workers = config.get('service_poller_workers', ServiceConfig.poller.workers)
        self.poller.frequency = config.get('service_poller_frequency', ServiceConfig.poller.frequency)
        self.discovery.enabled = config.get('service_discovery_enabled', True)   # unused
        self.discovery.workers = config.get('service_discovery_workers', ServiceConfig.discovery.workers)
        self.discovery.frequency = config.get('service_discovery_frequency', ServiceConfig.discovery.frequency)
        self.services.enabled = config.get('service_services_enabled', True)
        self.services.workers = config.get('service_services_workers', ServiceConfig.services.workers)
        self.services.frequency = config.get('service_services_frequency', ServiceConfig.services.frequency)
        self.billing.enabled = config.get('service_billing_enabled', True)
        self.billing.frequency = config.get('service_billing_frequency', ServiceConfig.billing.frequency)
        self.billing.calculate = config.get('service_billing_calculate_frequency', ServiceConfig.billing.calculate)
        self.alerting.enabled = config.get('service_alerting_enabled', True)
        self.alerting.frequency = config.get('service_alerting_frequency', ServiceConfig.alerting.frequency)
        self.ping.enabled = config.get('service_ping_enabled', False)
        self.ping.frequency = config.get('ping_rrd_step', ServiceConfig.billing.calculate)
        self.down_retry = config.get('service_poller_down_retry', ServiceConfig.down_retry)
        self.log_level = config.get('service_loglevel', ServiceConfig.log_level)
        self.update_enabled = config.get('service_update_enabled', ServiceConfig.update_enabled)
        self.update_frequency = config.get('service_update_frequency', ServiceConfig.update_frequency)

        self.redis_host = os.getenv('REDIS_HOST', config.get('redis_host', ServiceConfig.redis_host))
        self.redis_db = os.getenv('REDIS_DB', config.get('redis_db', ServiceConfig.redis_db))
        self.redis_pass = os.getenv('REDIS_PASSWORD', config.get('redis_pass', ServiceConfig.redis_pass))
        self.redis_port = int(os.getenv('REDIS_PORT', config.get('redis_port', ServiceConfig.redis_port)))
        self.redis_socket = os.getenv('REDIS_SOCKET', config.get('redis_socket', ServiceConfig.redis_socket))
        self.redis_sentinel = os.getenv('REDIS_SENTINEL', config.get('redis_sentinel', ServiceConfig.redis_sentinel))
        self.redis_sentinel_service = os.getenv('REDIS_SENTINEL_SERVICE',
                                                config.get('redis_sentinel_service',
                                                           ServiceConfig.redis_sentinel_service))

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
    queue_managers = {}
    poller_manager = None
    discovery_manager = None
    last_poll = {}
    terminate_flag = False
    db_failures = 0

    def __init__(self):
        self.config.populate()
        threading.current_thread().name = self.config.name  # rename main thread

        self.attach_signals()

        self._db = LibreNMS.DB(self.config)

        self._lm = self.create_lock_manager()
        self.daily_timer = LibreNMS.RecurringTimer(self.config.update_frequency, self.run_maintenance, 'maintenance')
        self.stats_timer = LibreNMS.RecurringTimer(self.config.poller.frequency, self.log_performance_stats, 'performance')
        self.is_master = False

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
        self.poller_manager = LibreNMS.PollerQueueManager(self.config, self._lm)
        self.queue_managers['poller'] = self.poller_manager
        self.discovery_manager = LibreNMS.DiscoveryQueueManager(self.config, self._lm)
        self.queue_managers['discovery'] = self.discovery_manager
        if self.config.alerting.enabled:
            self.queue_managers['alerting'] = LibreNMS.AlertQueueManager(self.config, self._lm)
        if self.config.services.enabled:
            self.queue_managers['services'] = LibreNMS.ServicesQueueManager(self.config, self._lm)
        if self.config.billing.enabled:
            self.queue_managers['billing'] = LibreNMS.BillingQueueManager(self.config, self._lm)
        if self.config.ping.enabled:
            self.queue_managers['ping'] = LibreNMS.PingQueueManager(self.config, self._lm)
        if self.config.update_enabled:
            self.daily_timer.start()
        self.stats_timer.start()

        info("LibreNMS Service: {} started!".format(self.config.unique_name))
        info("Poller group {}. Using Python {} and {} locks and queues"
             .format('0 (default)' if self.config.group == [0] else self.config.group, python_version(),
                     'redis' if isinstance(self._lm, LibreNMS.RedisLock) else 'internal'))
        if self.config.update_enabled:
            info("Maintenance tasks will be run every {}".format(timedelta(seconds=self.config.update_frequency)))
        else:
            warning("Maintenance tasks are disabled.")

        # Main dispatcher loop
        try:
            while not self.terminate_flag:
                master_lock = self._acquire_master()
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

    def _acquire_master(self):
        return self._lm.lock('dispatch.master', self.config.unique_name, self.config.master_timeout, True)

    def _release_master(self):
        self._lm.unlock('dispatch.master', self.config.unique_name)

    # ------------ Discovery ------------
    def dispatch_immediate_discovery(self, device_id, group):
        if not self.discovery_manager.is_locked(device_id):
            self.discovery_manager.post_work(device_id, group)

    # ------------ Polling ------------
    def dispatch_immediate_polling(self, device_id, group):
        if not self.poller_manager.is_locked(device_id):
            self.poller_manager.post_work(device_id, group)

            if self.config.debug:
                cur_time = time.time()
                elapsed = cur_time - self.last_poll.get(device_id, cur_time)
                self.last_poll[device_id] = cur_time
                # arbitrary limit to reduce spam
                if elapsed > (self.config.poller.frequency - self.config.master_resolution):
                    debug("Dispatching polling for device {}, time since last poll {:.2f}s"
                          .format(device_id, elapsed))

    def fetch_immediate_device_list(self):
        try:
            poller_find_time = self.config.poller.frequency - 1
            discovery_find_time = self.config.discovery.frequency - 1

            result = self._db.query('''SELECT `device_id`,
                  `poller_group`,
                  COALESCE(`last_polled` <= DATE_ADD(DATE_ADD(NOW(), INTERVAL -%s SECOND), INTERVAL `last_polled_timetaken` SECOND), 1) AS `poll`,
                  IF(snmp_disable=1 OR status=0, 0, COALESCE(`last_discovered` <= DATE_ADD(DATE_ADD(NOW(), INTERVAL -%s SECOND), INTERVAL `last_discovered_timetaken` SECOND), 1)) AS `discover`
                FROM `devices`
                WHERE `disabled` = 0 AND (
                    `last_polled` IS NULL OR
                    `last_discovered` IS NULL OR
                    `last_polled` <= DATE_ADD(DATE_ADD(NOW(), INTERVAL -%s SECOND), INTERVAL `last_polled_timetaken` SECOND) OR
                    `last_discovered` <= DATE_ADD(DATE_ADD(NOW(), INTERVAL -%s SECOND), INTERVAL `last_discovered_timetaken` SECOND)
                )
                ORDER BY `last_polled_timetaken` DESC''', (poller_find_time, discovery_find_time, poller_find_time, discovery_find_time))
            self.db_failures = 0
            return result
        except pymysql.err.Error:
            self.db_failures += 1
            if self.db_failures > self.config.max_db_failures:
                warning("Too many DB failures ({}), attempting to release master".format(self.db_failures))
                self._release_master()
                sleep(self.config.master_resolution)  # sleep to give another node a chance to acquire
            return []

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
        output = LibreNMS.call_script('daily.sh')
        info("Maintenance tasks complete\n{}".format(output))

        self.restart()

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
                                      unix_socket_path=self.config.redis_socket,
                                      sentinel=self.config.redis_sentinel,
                                      sentinel_service=self.config.redis_sentinel_service)
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
        self._release_master()

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
        self._release_master()

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
        for manager in self.queue_managers.values():
            try:
                manager.start_dispatch()
            except AttributeError:
                pass

    def stop_dispatch_timers(self):
        """
        Stop all dispatch timers, this should be called when we are no longer the master dispatcher.
        """
        for manager in self.queue_managers.values():
            try:
                manager.stop_dispatch()
            except AttributeError:
                pass

    def _stop_managers_and_wait(self):
        """
        Stop all QueueManagers, and wait for their processing threads to complete.
        We send the stop signal to all QueueManagers first, then wait for them to finish.
        """
        for manager in self.queue_managers.values():
            manager.stop()

        for manager in self.queue_managers.values():
            manager.stop_and_wait()

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

            for worker_type, manager in self.queue_managers.items():
                worker_seconds, devices = manager.performance.reset()

                # Record the queue state
                self._db.query('INSERT INTO poller_cluster_stats(parent_poller, poller_type, depth, devices, worker_seconds, workers, frequency) '
                               'values(@parent_poller_id, "{0}", {1}, {2}, {3}, {4}, {5}) '
                               'ON DUPLICATE KEY UPDATE depth={1}, devices={2}, worker_seconds={3}, workers={4}, frequency={5}; '
                               .format(worker_type,
                                       sum([manager.get_queue(group).qsize() for group in self.config.group]),
                                       devices,
                                       worker_seconds,
                                       getattr(self.config, worker_type).workers,
                                       getattr(self.config, worker_type).frequency)
                               )
        except pymysql.err.Error:
            exception("Unable to log performance statistics - is the database still online?")
