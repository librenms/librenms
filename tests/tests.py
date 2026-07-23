import gc
import logging
import os
import tempfile
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

smoke_logger = logging.getLogger("memory_pressure_smoke")


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


class TestMemoryFraction(unittest.TestCase):
    """Deterministic fixtures for QueueManager.memory_fraction (no real cgroups)."""

    from LibreNMS.queuemanager import QueueManager as QM

    def setUp(self):
        self.tmp = tempfile.mkdtemp()

    def _cg(self):
        return path.join(self.tmp, "cg")

    def _write(self, rel, content):
        p = path.join(self._cg(), rel)
        os.makedirs(path.dirname(p), exist_ok=True)
        with open(p, "w") as fh:
            fh.write(content)

    def _meminfo(self, total, avail):
        p = path.join(self.tmp, "meminfo")
        with open(p, "w") as fh:
            fh.write("MemTotal:       %d kB\nMemAvailable:   %d kB\n" % (total, avail))
        return p

    def test_cgroup_v2_stressed(self):
        self._write("memory.current", "920000\n")
        self._write("memory.max", "1000000\n")
        self.assertAlmostEqual(self.QM._mem_frac_cgv2(self._cg()), 0.92)

    def test_cgroup_v2_unlimited_is_none(self):
        self._write("memory.current", "500\n")
        self._write("memory.max", "max\n")
        self.assertIsNone(self.QM._mem_frac_cgv2(self._cg()))

    def test_cgroup_v1_stressed(self):
        self._write("memory/memory.usage_in_bytes", "900\n")
        self._write("memory/memory.limit_in_bytes", "1000\n")
        self.assertAlmostEqual(self.QM._mem_frac_cgv1(self._cg()), 0.9)

    def test_cgroup_v1_unlimited_is_none(self):
        self._write("memory/memory.usage_in_bytes", "900\n")
        self._write("memory/memory.limit_in_bytes", "9223372036854771712\n")
        self.assertIsNone(self.QM._mem_frac_cgv1(self._cg()))

    def test_bare_metal_meminfo(self):
        self.assertAlmostEqual(
            self.QM._mem_frac_meminfo(self._meminfo(2000, 500)), 0.75
        )

    def test_full_fallback_to_meminfo_when_no_cgroup(self):
        # empty cgroup dir -> v2/v1 None -> meminfo used
        self.assertAlmostEqual(
            self.QM.memory_fraction(self._cg(), self._meminfo(1000, 250)), 0.75
        )

    def test_nothing_readable_returns_none(self):
        self.assertIsNone(
            self.QM.memory_fraction(self._cg(), path.join(self.tmp, "nope"))
        )

    def test_zero_limit_no_div_by_zero(self):
        self._write("memory.current", "5\n")
        self._write("memory.max", "0\n")
        self.assertIsNone(self.QM._mem_frac_cgv2(self._cg()))


def _current_cgroup_v2_dir():
    """Resolve this process's cgroup v2 directory, or None if not cgroup v2."""
    try:
        with open("/proc/self/cgroup") as fh:
            for line in fh:
                parts = line.strip().split(":", 2)
                if parts[0] == "0" and parts[1] == "":  # "0::/path" == cgroup v2
                    return "/sys/fs/cgroup" + parts[2]
    except OSError:
        pass
    return None


@unittest.skipUnless(
    os.getenv("LIBRENMS_MEM_SMOKE"),
    "memory-pressure smoke: set LIBRENMS_MEM_SMOKE=1 and run inside "
    "`systemd-run --scope -p MemoryMax=256M -p MemorySwapMax=0` to execute",
)
class TestMemoryPressureSmoke(unittest.TestCase):
    """End-to-end: memory_fraction() tracks a REAL cgroup v2 limit and the
    pause/resume thresholds are crossed under genuine allocation.

    Skipped by default (allocates real memory, needs a memory-limited cgroup);
    this is the harness that produced the manual confidence, kept in-tree so it
    is reviewable and reproducible rather than living only in a paste.
    """

    def test_pause_and_resume_under_real_pressure(self):
        from LibreNMS.queuemanager import QueueManager as QM

        # Make the trajectory visible under a plain `python3 -m unittest` run
        # (unittest does not configure logging). Opt-in test only; scoped to our
        # own logger so normal/CI runs and root logging are untouched.
        smoke_logger.setLevel(logging.INFO)
        if not smoke_logger.handlers:
            _h = logging.StreamHandler(sys.stderr)
            _h.setFormatter(logging.Formatter("%(message)s"))
            smoke_logger.addHandler(_h)
            smoke_logger.propagate = False

        cg = _current_cgroup_v2_dir()
        self.assertIsNotNone(cg, "not running under cgroup v2")
        self.assertIsNotNone(
            QM._mem_read_int(path.join(cg, "memory.max")),
            "no real memory.max; run inside a memory-limited scope",
        )

        pause_frac = 0.85
        resume_frac = pause_frac - 0.10
        chunk = 16 * 1024 * 1024
        blocks = []
        paused = False

        for _ in range(64):
            buf = bytearray(chunk)
            for j in range(0, chunk, 4096):
                buf[j] = 1  # touch pages so they are actually resident
            blocks.append(buf)
            frac = QM.memory_fraction(cgroup_root=cg)
            self.assertIsNotNone(frac, "probe returned None inside a limited scope")
            if frac >= pause_frac:
                paused = True
                smoke_logger.info(
                    "PAUSE at %.1f%% (%d MiB allocated, limit %d MiB)",
                    frac * 100,
                    len(blocks) * (chunk // (1024 * 1024)),
                    QM._mem_read_int(path.join(cg, "memory.max")) // (1024 * 1024),
                )
                break
            smoke_logger.info("allocating: %.1f%%", frac * 100)
        self.assertTrue(paused, "allocation never reached the pause threshold")

        frac = QM.memory_fraction(cgroup_root=cg)
        while blocks:
            del blocks[len(blocks) // 2 :]
            gc.collect()
            frac = QM.memory_fraction(cgroup_root=cg)
            smoke_logger.info("freeing: %.1f%%", frac * 100)
            if frac < resume_frac:
                smoke_logger.info("RESUME crossed at %.1f%%", frac * 100)
                break
        self.assertLess(frac, resume_frac, "freeing memory never crossed resume")


if __name__ == "__main__":
    unittest.main()
