import pymysql
import subprocess
import threading
import traceback
from logging import debug, info, error, critical, warning
from queue import Empty
from subprocess import CalledProcessError

import LibreNMS


class QueueManager:
    def __init__(self, config, lock_manager, type_desc, uses_groups=False, auto_start=True):
        """
        This class manages a queue of jobs and can be used to submit jobs to the queue with post_work()
        and process jobs in that queue in worker threads using the work_function
        This will attempt to use redis to create a queue, but fall back to an internal queue.
        If you are using redis, you can have multiple QueueManagers working on the same queue

        You can start or stop the worker threads with start(), stop(), and stop_and_wait()

        :param config: LibreNMS.ServiceConfig reference to the service config object
        :param lock_manager: A LibreNMS.Lock instance to help with locks
        :param type_desc: description for this queue manager type
        :param work_function: function that will be called to perform the task
        :param auto_start: automatically start worker threads
        """
        self.type = type_desc
        self.uses_groups = uses_groups
        self.config = config
        self.performance = LibreNMS.PerformanceCounter()

        self._threads = []
        self._queues = {}
        self._queue_create_lock = threading.Lock()
        self._lm = lock_manager

        self._stop_event = threading.Event()

        info("Groups: {}".format(self.config.group))
        info("{} QueueManager created: {} workers, {}s frequency"
             .format(self.type.title(), self.get_poller_config().workers, self.get_poller_config().frequency))

        if auto_start:
            self.start()

    def _service_worker(self, queue_id):
        debug("Worker started {}".format(threading.current_thread().getName()))
        while not self._stop_event.is_set():
            debug("Worker {} checking queue {} ({}) for work".format(threading.current_thread().getName(), queue_id,
                                                                     self.get_queue(queue_id).qsize()))
            try:
                # cannot break blocking request with redis-py, so timeout :(
                device_id = self.get_queue(queue_id).get(True, 10)

                if device_id is not None:  # None returned by redis after timeout when empty
                    debug(
                        "Worker {} ({}) got work {} ".format(threading.current_thread().getName(), queue_id, device_id))
                    with LibreNMS.TimeitContext.start() as t:
                        debug("Queues: {}".format(self._queues))
                        target_desc = "{} ({})".format(device_id if device_id else '',
                                                       queue_id) if queue_id else device_id
                        self.do_work(device_id, queue_id)

                        runtime = t.delta()
                        info("Completed {} run for {} in {:.2f}s".format(self.type, target_desc, runtime))
                        self.performance.add(runtime)
            except Empty:
                pass  # ignore empty queue exception from subprocess.Queue
            except CalledProcessError as e:
                error('{} poller script error! {} returned {}: {}'
                      .format(self.type.title(), e.cmd, e.returncode, e.output))
            except Exception as e:
                error('{} poller exception! {}'.format(self.type.title(), e))
                traceback.print_exc()

    def post_work(self, payload, queue_id):
        """
        Post work to the the queue group.
        :param payload: string payload to deliver to the worker
        :param queue_id: which queue to post to, 0 is the default
        """
        self.get_queue(queue_id).put(payload)
        debug("Posted work for {} to {}:{} queue size: {}"
              .format(payload, self.type, queue_id, self.get_queue(queue_id).qsize()))

    def start(self):
        """
        Start worker threads
        """
        workers = self.get_poller_config().workers
        groups = self.config.group if hasattr(self.config.group, "__iter__") else [self.config.group]
        if self.uses_groups:
            for group in groups:
                group_workers = max(int(workers / len(groups)), 1)
                for i in range(group_workers):
                    thread_name = "{}_{}-{}".format(self.type.title(), group, i + 1)
                    self.spawn_worker(thread_name, group)

                debug("Started {} {} threads for group {}".format(group_workers, self.type, group))
        else:
            self.spawn_worker(self.type.title(), 0)

    def do_work(self, device_id, group):
        pass

    def spawn_worker(self, thread_name, group):
        pt = threading.Thread(target=self._service_worker, name=thread_name,
                              args=(group,))
        pt.daemon = True
        self._threads.append(pt)
        pt.start()

    def restart(self):
        """
        Stop the worker threads and wait for them to finish. Then start them again.
        """
        self.stop_and_wait()
        self.start()

    def stop(self):
        """
        Stop the worker threads, does not wait for them to finish.
        """
        self._stop_event.set()

    def stop_and_wait(self):
        """
        Stop the worker threads and wait for them to finish.
        """
        self.stop()  # make sure this has been called so we don't block forever
        for t in self._threads:
            t.join()
        del self._threads[:]

    def get_poller_config(self):
        """
        Returns the LibreNMS.PollerConfig for this QueueManager
        :return: LibreNMS.PollerConfig
        """
        return getattr(self.config, self.type)

    def get_queue(self, group):
        name = self.queue_name(self.type, group)

        if name not in self._queues.keys():
            with self._queue_create_lock:
                if name not in self._queues.keys():
                    self._queues[name] = self._create_queue(self.type, group)

        return self._queues[name]

    def _create_queue(self, queue_type, group):
        """
        Create a queue (not thread safe)
        :param queue_type:
        :param group:
        :return:
        """
        info("Creating queue {}".format(self.queue_name(queue_type, group)))
        try:
            return LibreNMS.RedisUniqueQueue(self.queue_name(queue_type, group),
                                             namespace='librenms.queue',
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
                exit(2)
        except Exception as e:
            if self.config.distributed:
                critical("ERROR: Redis connection required for distributed polling")
                critical("Could not connect to Redis. {}".format(e))
                exit(2)

        return LibreNMS.UniqueQueue()

    @staticmethod
    def queue_name(queue_type, group):
        if queue_type and type(group) == int:
            return "{}:{}".format(queue_type, group)
        else:
            raise ValueError("Refusing to create improperly scoped queue - parameters were invalid or not set")

    def record_runtime(self, duration):
        self.performance.add(duration)

    # ------ Locking Helpers ------
    def lock(self, context, context_name='device', allow_relock=False, timeout=0):
        return self._lm.lock(self._gen_lock_name(context, context_name), self._gen_lock_owner(), timeout, allow_relock)

    def unlock(self, context, context_name='device'):
        return self._lm.unlock(self._gen_lock_name(context, context_name), self._gen_lock_owner())

    def is_locked(self, context, context_name='device'):
        return self._lm.check_lock(self._gen_lock_name(context, context_name))

    def _gen_lock_name(self, context, context_name):
        return '{}.{}.{}'.format(self.type, context_name, context)

    def _gen_lock_owner(self):
        return "{}-{}".format(self.config.unique_name, threading.current_thread().name)


class TimedQueueManager(QueueManager):
    def __init__(self, config, lock_manager, type_desc, uses_groups=False, auto_start=True):
        """
        A queue manager that periodically dispatches work to the queue
        The times are normalized like they started at 0:00
        :param config: LibreNMS.ServiceConfig reference to the service config object
        :param type_desc: description for this queue manager type
        :param uses_groups: If this queue respects assigned groups or there is only one group
        :param auto_start: automatically start worker threads
        """
        QueueManager.__init__(self, config, lock_manager, type_desc, uses_groups, auto_start)
        self.timer = LibreNMS.RecurringTimer(self.get_poller_config().frequency, self.do_dispatch)

    def start_dispatch(self):
        """
        Start the dispatch timer, this is not called automatically on init
        """
        self.timer.start()

    def stop_dispatch(self):
        """
        Stop the dispatch timer
        """
        self.timer.stop()

    def stop(self):
        """
        Stop the worker threads and dispatcher thread, does not wait for them to finish.
        """
        self.stop_dispatch()
        QueueManager.stop(self)

    def do_dispatch(self):
        pass


class BillingQueueManager(TimedQueueManager):
    def __init__(self, config, lock_manager):
        """
        A TimedQueueManager with two timers dispatching poll billing and calculate billing to the same work queue

        :param config: LibreNMS.ServiceConfig reference to the service config object
        :param lock_manager: the single instance of lock manager
        """
        TimedQueueManager.__init__(self, config, lock_manager, 'billing')
        self.calculate_timer = LibreNMS.RecurringTimer(self.get_poller_config().calculate,
                                                       self.dispatch_calculate_billing, 'calculate_billing_timer')

    def start_dispatch(self):
        """
        Start the dispatch timer, this is not called automatically on init
        """
        self.calculate_timer.start()
        TimedQueueManager.start_dispatch(self)

    def stop_dispatch(self):
        """
        Stop the dispatch timer
        """
        self.calculate_timer.stop()
        TimedQueueManager.stop_dispatch(self)

    def dispatch_calculate_billing(self):
        self.post_work('calculate', 0)

    def do_dispatch(self):
        self.post_work('poll', 0)

    def do_work(self, run_type, group):
        if run_type == 'poll':
            info("Polling billing")
            LibreNMS.call_script('poll-billing.php')
        else:  # run_type == 'calculate'
            info("Calculating billing")
            LibreNMS.call_script('billing-calculate.php')


class PingQueueManager(TimedQueueManager):
    def __init__(self, config, lock_manager):
        """
        A TimedQueueManager to manage dispatch and workers for Ping

        :param config: LibreNMS.ServiceConfig reference to the service config object
        :param lock_manager: the single instance of lock manager
        """
        TimedQueueManager.__init__(self, config, lock_manager, 'ping', True)
        self._db = LibreNMS.DB(self.config)

    def do_dispatch(self):
        try:
            groups = self._db.query("SELECT DISTINCT (`poller_group`) FROM `devices`")
            for group in groups:
                self.post_work('', group[0])
        except pymysql.err.Error as e:
            critical("DB Exception ({})".format(e))

    def do_work(self, context, group):
        if self.lock(group, 'group', timeout=self.config.ping.frequency):
            try:
                info("Running fast ping")
                LibreNMS.call_script('ping.php', ('-g', group))
            finally:
                self.unlock(group, 'group')


class ServicesQueueManager(TimedQueueManager):
    def __init__(self, config, lock_manager):
        """
        A TimedQueueManager to manage dispatch and workers for Services

        :param config: LibreNMS.ServiceConfig reference to the service config object
        :param lock_manager: the single instance of lock manager
        """
        TimedQueueManager.__init__(self, config, lock_manager, 'services', True)
        self._db = LibreNMS.DB(self.config)

    def do_dispatch(self):
        try:
            devices = self._db.query("SELECT DISTINCT(`device_id`), `poller_group` FROM `services`"
                                     " LEFT JOIN `devices` USING (`device_id`) WHERE `disabled`=0")
            for device in devices:
                self.post_work(device[0], device[1])
        except pymysql.err.Error as e:
            critical("DB Exception ({})".format(e))

    def do_work(self, device_id, group):
        if self.lock(device_id, timeout=self.config.services.frequency):
            try:
                info("Checking services on device {}".format(device_id))
                LibreNMS.call_script('check-services.php', ('-h', device_id))
            except subprocess.CalledProcessError as e:
                if e.returncode == 5:
                    info("Device {} is down, cannot poll service, waiting {}s for retry"
                         .format(device_id, self.config.down_retry))
                    self.lock(device_id, allow_relock=True, timeout=self.config.down_retry)
            else:
                self.unlock(device_id)


class AlertQueueManager(TimedQueueManager):
    def __init__(self, config, lock_manager):
        """
        A TimedQueueManager to manage dispatch and workers for Alerts

        :param config: LibreNMS.ServiceConfig reference to the service config object
        :param lock_manager: the single instance of lock manager
        """
        TimedQueueManager.__init__(self, config, lock_manager, 'alerting')
        self._db = LibreNMS.DB(self.config)

    def do_dispatch(self):
        self.post_work('alerts', 0)

    def do_work(self, device_id, group):
        try:
            info("Checking alerts")
            LibreNMS.call_script('alerts.php')
        except subprocess.CalledProcessError as e:
            if e.returncode == 1:
                warning("There was an error issuing alerts: {}".format(e.output))
            else:
                raise


class PollerQueueManager(QueueManager):
    def __init__(self, config, lock_manager):
        """
        A TimedQueueManager to manage dispatch and workers for Alerts

        :param config: LibreNMS.ServiceConfig reference to the service config object
        :param lock_manager: the single instance of lock manager
        """
        QueueManager.__init__(self, config, lock_manager, 'poller', True)

    def do_work(self, device_id, group):
        if self.lock(device_id, timeout=self.config.poller.frequency):
            info('Polling device {}'.format(device_id))

            try:
                LibreNMS.call_script('poller.php', ('-h', device_id))
            except subprocess.CalledProcessError as e:
                if e.returncode == 6:
                    warning('Polling device {} unreachable, waiting {}s for retry'.format(device_id,
                                                                                          self.config.down_retry))
                    # re-lock to set retry timer
                    self.lock(device_id, allow_relock=True, timeout=self.config.down_retry)
                else:
                    error('Polling device {} failed! {}'.format(device_id, e))
                    self.unlock(device_id)
            else:
                self.unlock(device_id)
        else:
            debug('Tried to poll {}, but it is locked'.format(device_id))


class DiscoveryQueueManager(TimedQueueManager):
    def __init__(self, config, lock_manager):
        """
        A TimedQueueManager to manage dispatch and workers for Alerts

        :param config: LibreNMS.ServiceConfig reference to the service config object
        :param lock_manager: the single instance of lock manager
        """
        TimedQueueManager.__init__(self, config, lock_manager, 'discovery', True)
        self._db = LibreNMS.DB(self.config)

    def do_dispatch(self):
        try:
            devices = self._db.query("SELECT `device_id`, `poller_group` FROM `devices` WHERE `disabled`=0")
            for device in devices:
                self.post_work(device[0], device[1])
        except pymysql.err.Error as e:
            critical("DB Exception ({})".format(e))

    def do_work(self, device_id, group):
        if self.lock(device_id, timeout=LibreNMS.normalize_wait(self.config.discovery.frequency)):
            try:
                info("Discovering device {}".format(device_id))
                LibreNMS.call_script('discovery.php', ('-h', device_id))
            except subprocess.CalledProcessError as e:
                if e.returncode == 5:
                    info("Device {} is down, cannot discover, waiting {}s for retry"
                         .format(device_id, self.config.down_retry))
                    self.lock(device_id, allow_relock=True, timeout=self.config.down_retry)
                else:
                    self.unlock(device_id)
            else:
                self.unlock(device_id)
