import random
import threading
import traceback
from logging import debug, info, error, critical
from multiprocessing import Queue
from subprocess import CalledProcessError

import sys

import LibreNMS

if sys.version_info[0] < 3:
    from Queue import Empty
else:
    from queue import Empty


class QueueManager:
    def __init__(self, config, type_desc, work_function, auto_start=True):
        """
        This class manages a queue of jobs and can be used to submit jobs to the queue with post_work()
        and process jobs in that queue in worker threads using the work_function
        This will attempt to use redis to create a queue, but fall back to an internal queue.
        If you are using redis, you can have multiple QueueManagers working on the same queue

        You can start or stop the worker threads with start(), stop(), and stop_and_wait()

        :param config: LibreNMS.ServiceConfig reference to the service config object
        :param type_desc: description for this queue manager type
        :param work_function: function that will be called to perform the task
        :param auto_start: automatically start worker threads
        """
        self.type = type_desc
        self.config = config

        self._threads = []
        self._queues = {}
        self._queue_create_lock = threading.Lock()

        self._work_function = work_function
        self._stop_event = threading.Event()

        info("{} QueueManager created: {} workers, {}s frequency"
             .format(self.type.title(), self.get_poller_config().workers, self.get_poller_config().frequency))

        if auto_start:
            self.start()

    def _service_worker(self, work_func, queue_id):
        while not self._stop_event.is_set():
            try:
                for queue in random.sample(queue_id, len(queue_id)):
                    # cannot break blocking request with redis-py, so timeout :(
                    device_id = self.get_queue(queue).get(True, 3)

                    if device_id:  # None returned by redis after timeout when empty
                        info("Worker attached to queues: {} removed job from queue {}".format(queue_id, queue))
                        work_func(device_id, queue)
            except Empty:
                pass  # ignore empty queue exception from subprocess.Queue
            except CalledProcessError as e:
                error('{} poller script error! {} returned {}: {}'
                      .format(self.type.title(), e.cmd, e.returncode, e.output))
            except Exception as e:
                error('{} poller exception! {}'.format(self.type.title(), e))
                traceback.print_exc()

    def post_work(self, payload='all', queue_id=0):
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
        workers = max(self.get_poller_config().workers, 1)
        for i in range(workers):
            thread_name = "{}-{}".format(self.type.title(), i + 1)
            pt = threading.Thread(target=self._service_worker, name=thread_name,
                                  args=(self._work_function, self.config.group))
            pt.daemon = True
            self._threads.append(pt)
            pt.start()
        debug("Started {} {} threads".format(workers, self.type))

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

    def get_queue(self, group=0):
        name = self.queue_name(self.type, group)

        if name not in self._queues.keys():
            with self._queue_create_lock:
                if name not in self._queues.keys():
                    self._queues[name] = self._create_queue(self.type, group)

        return self._queues[name]

    def _create_queue(self, type, group=0):
        """
        Create a queue (not thread safe)
        :param name:
        :param group:
        :return:
        """
        debug("Creating queue {}".format(self.queue_name(type, group)))
        try:
            return LibreNMS.RedisQueue(self.queue_name(type, group),
                                       namespace='librenms.queue',
                                       host=self.config.redis_host,
                                       port=self.config.redis_port,
                                       db=self.config.redis_db,
                                       password=self.config.redis_pass,
                                       unix_socket_path=self.config.redis_socket
                                       )
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

        return Queue()

    @staticmethod
    def queue_name(type, group):
        return "{}:{}".format(type, group)


class TimedQueueManager(QueueManager):
    def __init__(self, config, type_desc, work_function, dispatch_function=None, auto_start=True):
        """
        A queue manager that periodically dispatches work to the queue
        The times are normalized like they started at 0:00
        :param config: LibreNMS.ServiceConfig reference to the service config object
        :param type_desc: description for this queue manager type
        :param work_function: function that will be called to perform the task
        :param dispatch_function: function that will be called when the timer is up, should call post_work()
        :param auto_start: automatically start worker threads
        """
        QueueManager.__init__(self, config, type_desc, work_function, auto_start)
        if not dispatch_function:
            dispatch_function = self.post_work
        self.timer = LibreNMS.RecurringTimer(self.get_poller_config().frequency, dispatch_function)

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


class BillingQueueManager(TimedQueueManager):
    def __init__(self, config, work_function, poll_dispatch_function, calculate_dispatch_function,
                 auto_start=True):
        """
        A TimedQueueManager with two timers dispatching poll billing and calculate billing to the same work queue

        :param config: LibreNMS.ServiceConfig reference to the service config object
        :param work_function: function that will be called to perform the task
        :param poll_dispatch_function: function that will be called when the timer is up, should call post_work()
        :param calculate_dispatch_function: function that will be called when the timer is up, should call post_work()
        :param auto_start: automatically start worker threads
        """
        TimedQueueManager.__init__(self, config, 'billing', work_function, poll_dispatch_function, auto_start)
        self.calculate_timer = LibreNMS.RecurringTimer(self.get_poller_config().calculate, calculate_dispatch_function)

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
