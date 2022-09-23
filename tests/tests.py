import threading
import unittest
from os import path

import sys
from time import sleep

try:
    import redis
except ImportError:
    print("Redis tests won't be run")
    pass

sys.path.append(path.dirname(path.dirname(path.abspath(__file__))))
import LibreNMS


class TestLocks(unittest.TestCase):
    def setUp(self):
        pass

    @staticmethod
    def lock_thread(manager, lock_name, expiration, unlock_sleep=0):
        manager.lock(lock_name, "lock_thread", expiration)

        if unlock_sleep:
            sleep(unlock_sleep)
            manager.unlock(lock_name, "lock_thread")

    def test_threading_lock(self):
        lm = LibreNMS.ThreadingLock()

        thread = threading.Thread(
            target=self.lock_thread, args=(lm, "first.lock", 2, 1)
        )
        thread.daemon = True
        thread.start()

        sleep(0.05)
        self.assertFalse(
            lm.lock("first.lock", "main_thread", 0),
            "Acquired lock when it is held by thread",
        )
        self.assertFalse(
            lm.unlock("first.lock", "main_thread"), "Unlocked lock main doesn't own"
        )

        sleep(1.1)
        self.assertTrue(
            lm.lock("first.lock", "main_thread", 1),
            "Could not acquire lock previously held by thread",
        )
        self.assertFalse(
            lm.lock("first.lock", "main_thread", 1, False),
            "Was able to re-lock a lock main owns",
        )
        self.assertTrue(
            lm.lock("first.lock", "main_thread", 1, True),
            "Could not re-lock a lock main owns",
        )
        self.assertTrue(lm.check_lock("first.lock"))
        self.assertTrue(
            lm.unlock("first.lock", "main_thread"), "Could not unlock lock main holds"
        )
        self.assertFalse(
            lm.unlock("first.lock", "main_thread"), "Unlocked an unlocked lock?"
        )
        self.assertFalse(lm.check_lock("first.lock"))

    def test_redis_lock(self):
        if "redis" not in sys.modules:
            self.assertTrue(True, "Skipped Redis tests")
        else:
            rc = redis.Redis()
            rc.delete("lock:redis.lock")  # make sure no previous data exists

            lm = LibreNMS.RedisLock(namespace="lock")
            thread = threading.Thread(
                target=self.lock_thread, args=(lm, "redis.lock", 2, 1)
            )
            thread.daemon = True
            thread.start()

            sleep(0.05)
            self.assertFalse(
                lm.lock("redis.lock", "main_thread", 1),
                "Acquired lock when it is held by thread",
            )
            self.assertFalse(
                lm.unlock("redis.lock", "main_thread"), "Unlocked lock main doesn't own"
            )

            sleep(1.1)
            self.assertTrue(
                lm.lock("redis.lock", "main_thread", 1),
                "Could not acquire lock previously held by thread",
            )
            self.assertFalse(
                lm.lock("redis.lock", "main_thread", 1), "Relocked an existing lock"
            )
            self.assertTrue(
                lm.lock("redis.lock", "main_thread", 1, True),
                "Could not re-lock a lock main owns",
            )
            self.assertTrue(
                lm.unlock("redis.lock", "main_thread"),
                "Could not unlock lock main holds",
            )
            self.assertFalse(
                lm.unlock("redis.lock", "main_thread"), "Unlocked an unlocked lock?"
            )

    def queue_thread(self, manager, expect, wait=True):
        self.assertEqual(expect, manager.get(wait), "Got unexpected data in thread")

    def test_redis_queue(self):
        if "redis" not in sys.modules:
            self.assertTrue(True, "Skipped Redis tests")
        else:
            rc = redis.Redis()
            rc.delete("queue:testing")  # make sure no previous data exists
            qm = LibreNMS.RedisUniqueQueue("testing", namespace="queue")

            thread = threading.Thread(target=self.queue_thread, args=(qm, None, False))
            thread.daemon = True
            thread.start()

            thread = threading.Thread(target=self.queue_thread, args=(qm, "2"))
            thread.daemon = True
            thread.start()
            qm.put(2)

            qm.put(3)
            qm.put(4)
            sleep(0.05)
            self.assertEqual(2, qm.qsize())
            self.assertEqual("3", qm.get())
            self.assertEqual("4", qm.get(), "Did not get second item in queue")
            self.assertEqual(
                None, qm.get_nowait(), "Did not get None when queue should be empty"
            )
            self.assertTrue(qm.empty(), "Queue should be empty")


class TestTimer(unittest.TestCase):
    def setUp(self):
        self.counter = 0

    def count(self):
        self.counter += 1

    def test_recurring_timer(self):
        self.assertEqual(0, self.counter)
        timer = LibreNMS.RecurringTimer(0.5, self.count)
        timer.start()
        self.assertEqual(0, self.counter)
        sleep(0.5)
        self.assertEqual(1, self.counter)
        self.assertEqual(1, self.counter)
        sleep(0.5)
        self.assertEqual(2, self.counter)
        timer.stop()
        self.assertTrue(timer._event.is_set())
        sleep(0.5)
        self.assertEqual(2, self.counter)
        timer.start()
        sleep(0.5)
        self.assertEqual(3, self.counter)
        timer.stop()


if __name__ == "__main__":
    unittest.main()
