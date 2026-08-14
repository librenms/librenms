import gc
import logging
import os
import tempfile
import threading
import unittest
from contextlib import ExitStack
from os import path

import sys
from time import monotonic, sleep

try:
    import redis
except ImportError:
    print("Redis tests won't be run")
    pass

sys.path.append(path.dirname(path.dirname(path.abspath(__file__))))
import LibreNMS
from LibreNMS.queuemanager import LockRenewer

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
    """Deterministic fixtures for MemoryPressureGate.memory_fraction (no real cgroups)."""

    from LibreNMS.queuemanager import MemoryPressureGate as Gate

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
        self.assertAlmostEqual(self.Gate._frac_cgv2(self._cg()), 0.92)

    def test_cgroup_v2_unlimited_is_none(self):
        self._write("memory.current", "500\n")
        self._write("memory.max", "max\n")
        self.assertIsNone(self.Gate._frac_cgv2(self._cg()))

    def test_cgroup_v1_stressed(self):
        self._write("memory/memory.usage_in_bytes", "900\n")
        self._write("memory/memory.limit_in_bytes", "1000\n")
        self.assertAlmostEqual(self.Gate._frac_cgv1(self._cg()), 0.9)

    def test_cgroup_v1_unlimited_is_none(self):
        self._write("memory/memory.usage_in_bytes", "900\n")
        self._write("memory/memory.limit_in_bytes", "9223372036854771712\n")
        self.assertIsNone(self.Gate._frac_cgv1(self._cg()))

    def test_bare_metal_meminfo(self):
        self.assertAlmostEqual(self.Gate._frac_meminfo(self._meminfo(2000, 500)), 0.75)

    def test_full_fallback_to_meminfo_when_no_cgroup(self):
        # empty cgroup dir -> v2/v1 None -> meminfo used
        self.assertAlmostEqual(
            self.Gate.memory_fraction(self._cg(), self._meminfo(1000, 250)), 0.75
        )

    def test_nothing_readable_returns_none(self):
        self.assertIsNone(
            self.Gate.memory_fraction(self._cg(), path.join(self.tmp, "nope"))
        )

    def test_zero_limit_no_div_by_zero(self):
        self._write("memory.current", "5\n")
        self._write("memory.max", "0\n")
        self.assertIsNone(self.Gate._frac_cgv2(self._cg()))


class TestMemoryPressureGate(unittest.TestCase):
    """Gate behaviour (config parsing + hysteresis), independent of real memory."""

    from LibreNMS.queuemanager import MemoryPressureGate as Gate

    class _Cfg:
        def __init__(self, pct):
            self.memory_pressure_percent = pct

    def _gate(self, limit_frac, fracs):
        """A gate whose memory_fraction() yields the given sequence in turn."""
        gate = self.Gate(limit_frac)
        seq = iter(fracs)
        gate.memory_fraction = lambda *a, **k: next(seq)
        return gate

    def test_disabled_when_unset(self):
        gate = self.Gate.from_config(self._Cfg(None))
        self.assertFalse(gate.enabled)
        self.assertFalse(gate.pause_if_pressured())

    def test_from_config_enabled(self):
        gate = self.Gate.from_config(self._Cfg(85))
        self.assertTrue(gate.enabled)

    def test_from_config_rejects_out_of_range(self):
        for bad in (0, 100, -1):
            with self.assertRaises(ValueError):
                self.Gate.from_config(self._Cfg(bad))

    def test_from_config_rejects_non_int(self):
        with self.assertRaises(ValueError):
            self.Gate.from_config(self._Cfg("high"))

    def test_pause_when_at_or_above_limit(self):
        # 90% usage against an 85% limit -> pause (stub sleep so it is instant)
        import LibreNMS.queuemanager as qm

        gate = self._gate(0.85, [0.90])
        orig_sleep = qm.time.sleep
        qm.time.sleep = lambda *_: None
        try:
            self.assertTrue(gate.pause_if_pressured())
        finally:
            qm.time.sleep = orig_sleep

    def test_hysteresis_holds_between_bands(self):
        # limit 85%: pause at >=85, stay paused through the 75-85 band, resume <75
        import LibreNMS.queuemanager as qm

        orig_sleep = qm.time.sleep
        qm.time.sleep = lambda *_: None
        try:
            gate = self._gate(0.85, [0.90, 0.80, 0.74])
            self.assertTrue(gate.pause_if_pressured())  # 90% -> pause
            self.assertTrue(gate.pause_if_pressured())  # 80% -> still paused
            self.assertFalse(gate.pause_if_pressured())  # 74% -> resume
        finally:
            qm.time.sleep = orig_sleep

    def test_fails_open_when_undeterminable(self):
        gate = self._gate(0.85, [None])
        self.assertFalse(gate.pause_if_pressured())


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
        from LibreNMS.queuemanager import MemoryPressureGate as Gate

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
            Gate._read_int(path.join(cg, "memory.max")),
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
            frac = Gate.memory_fraction(cgroup_root=cg)
            self.assertIsNotNone(frac, "probe returned None inside a limited scope")
            if frac >= pause_frac:
                paused = True
                smoke_logger.info(
                    "PAUSE at %.1f%% (%d MiB allocated, limit %d MiB)",
                    frac * 100,
                    len(blocks) * (chunk // (1024 * 1024)),
                    Gate._read_int(path.join(cg, "memory.max")) // (1024 * 1024),
                )
                break
            smoke_logger.info("allocating: %.1f%%", frac * 100)
        self.assertTrue(paused, "allocation never reached the pause threshold")

        frac = Gate.memory_fraction(cgroup_root=cg)
        while blocks:
            del blocks[len(blocks) // 2 :]
            gc.collect()
            frac = Gate.memory_fraction(cgroup_root=cg)
            smoke_logger.info("freeing: %.1f%%", frac * 100)
            if frac < resume_frac:
                smoke_logger.info("RESUME crossed at %.1f%%", frac * 100)
                break
        self.assertLess(frac, resume_frac, "freeing memory never crossed resume")
class _FakeConfig:
    """Minimal ServiceConfig stand-in: LockRenewer reads one attribute."""

    def __init__(self, poller_renew_locks):
        self.poller_renew_locks = poller_renew_locks


class RecordingLockManager:
    """Wraps a real ThreadingLock and records every lock() call.

    Records rather than fakes, so the TTL and owner assertions are made
    against what the renewer genuinely asked for.
    """

    def __init__(self):
        self._inner = LibreNMS.ThreadingLock()
        self.calls = []
        self._mutex = threading.Lock()

    def lock(self, name, owner, expiration=1, allow_owner_relock=False):
        with self._mutex:
            self.calls.append((name, owner, expiration, allow_owner_relock))
        return self._inner.lock(name, owner, expiration, allow_owner_relock)

    def unlock(self, name, owner):
        return self._inner.unlock(name, owner)

    def check_lock(self, name):
        return self._inner.check_lock(name)

    def calls_for(self, name):
        with self._mutex:
            return [call for call in self.calls if call[0] == name]


class HijackedLockManager(RecordingLockManager):
    """lock() always fails, as if another node had taken the lock."""

    def lock(self, name, owner, expiration=1, allow_owner_relock=False):
        RecordingLockManager.lock(self, name, owner, expiration, allow_owner_relock)
        return False


class ExplodingLockManager(RecordingLockManager):
    """lock() always raises, as if redis had gone away."""

    def lock(self, name, owner, expiration=1, allow_owner_relock=False):
        RecordingLockManager.lock(self, name, owner, expiration, allow_owner_relock)
        raise RuntimeError("redis went away")


class SelectivelyExplodingLockManager(RecordingLockManager):
    """lock() raises for names ending in .bad, succeeds for the rest."""

    def lock(self, name, owner, expiration=1, allow_owner_relock=False):
        RecordingLockManager.lock(self, name, owner, expiration, allow_owner_relock)
        if name.endswith(".bad"):
            raise RuntimeError("redis went away for this key")
        return True


class RecordingHandler(logging.Handler):
    """Captures log records so tests can assert on them."""

    def __init__(self):
        logging.Handler.__init__(self)
        self.records = []

    def emit(self, record):
        self.records.append(record)

    def messages(self, min_level=0):
        return [r.getMessage() for r in self.records if r.levelno >= min_level]


class TestLockRenewer(unittest.TestCase):
    """Behaviour of the poller lock renewer.

    Timing-based, like TestLocks above. LockRenewer scans for due locks every
    _RESOLUTION (1s) and renews each at a third of its TTL, so worst-case
    renewal latency is interval + 1s: every hold below has to clear that, with
    a tick of margin on top -- a hold that only just clears it fails on a
    loaded machine.

    _renew_once() and _loop() both catch Exception and log it, so a broken
    pass surfaces as a log line rather than a traceback. These tests therefore
    assert against captured records -- "nothing raised" proves nothing here.
    """

    def _renewer(self, enabled, lock_manager):
        renewer = LockRenewer.from_config(_FakeConfig(enabled), lock_manager)
        self.addCleanup(renewer.stop)
        return renewer

    def _capture_logs(self):
        handler = RecordingHandler()
        logger = logging.getLogger("LibreNMS.queuemanager")
        previous_level = logger.level
        logger.addHandler(handler)
        logger.setLevel(logging.DEBUG)

        def restore():
            logger.removeHandler(handler)
            logger.setLevel(previous_level)

        self.addCleanup(restore)
        return handler

    def test_disabled_renewer_starts_no_thread(self):
        lm = RecordingLockManager()
        renewer = self._renewer(False, lm)
        threads_before = threading.active_count()
        renewer.start()

        self.assertFalse(renewer.enabled)
        self.assertEqual(
            threads_before,
            threading.active_count(),
            "Disabled renewer spawned a keeper thread",
        )

        with renewer.keep("some.lock", "owner-a", 3):
            sleep(1.2)  # past _RESOLUTION: a tick would have fired by now
        self.assertEqual([], lm.calls, "Disabled keep() touched the lock manager")

    def test_from_config_reads_env_var_strings(self):
        cases = [
            ("1", True),
            ("true", True),
            ("TRUE", True),
            ("yes", True),
            ("on", True),
            (" on ", True),
            ("0", False),
            ("false", False),
            ("", False),
            ("maybe", False),
            (True, True),
            (False, False),
        ]
        for raw, expected in cases:
            with self.subTest(raw=raw):
                renewer = LockRenewer.from_config(
                    _FakeConfig(raw), RecordingLockManager()
                )
                self.assertEqual(expected, renewer.enabled)

    def test_renews_a_held_lock_while_polling(self):
        lm = RecordingLockManager()
        renewer = self._renewer("1", lm)
        renewer.start()
        self.assertTrue(renewer._thread.daemon, "Keeper thread must not block exit")

        lm.lock("poller.device.42", "node-Poller_1", 3)  # the worker's own take
        with renewer.keep("poller.device.42", "node-Poller_1", 3):
            sleep(3.5)  # ttl 3 => renew every 1s => at least two renewals
        renewals = [call for call in lm.calls if call[3] is True]

        self.assertGreaterEqual(
            len(renewals), 2, "Lock was not renewed: {}".format(lm.calls)
        )
        for call in renewals:
            self.assertEqual(("poller.device.42", "node-Poller_1", 3, True), call)
        self.assertTrue(
            lm.check_lock("poller.device.42"), "Lock lapsed while the poll was running"
        )

        calls_at_exit = len(lm.calls)
        sleep(1.5)
        self.assertEqual(
            calls_at_exit,
            len(lm.calls),
            "Renewals continued after the keep() block exited",
        )

    def test_down_retry_ttl_survives_a_failed_poll(self):
        # Mirrors PollerQueueManager.do_work with renewal enabled: renew at the
        # poller frequency inside the block, then re-lock to down_retry outside
        # it. The bug this pins is a keeper tick landing after that re-lock and
        # stamping the short cooldown back up to the poller frequency, which
        # would suppress the next attempt at the device.
        frequency, down_retry = 3, 1  # scaled from 300/60 to keep this quick
        name, owner = "poller.device.99", "node-Poller_2"
        lm = RecordingLockManager()
        renewer = self._renewer("1", lm)
        renewer.start()

        lm.lock(name, owner, frequency)
        with renewer.keep(name, owner, frequency):
            sleep(2.2)
        calls_at_exit = len(lm.calls)
        lm.lock(name, owner, down_retry, True)  # do_work's exit-6 re-lock
        sleep(2.0)  # two keeper ticks would have fired in here

        self.assertEqual(
            [],
            lm.calls[calls_at_exit + 1 :],
            "Renewer touched the lock after the down_retry re-lock",
        )
        self.assertFalse(
            lm.check_lock(name),
            "down_retry TTL was stamped back up to the poller frequency",
        )

    def test_failed_renewal_is_logged(self):
        logs = self._capture_logs()
        lm = HijackedLockManager()
        renewer = self._renewer("1", lm)
        renewer.start()

        with renewer.keep("poller.device.7", "node-Poller_3", 3):
            sleep(3.0)  # clears interval (1s) + _RESOLUTION (1s), with margin

        warnings = [
            message
            for message in logs.messages(logging.WARNING)
            if "renew" in message.lower()
        ]
        self.assertTrue(warnings, "A lost lock was silent: {}".format(logs.messages()))
        self.assertTrue(
            any("poller.device.7" in message for message in warnings),
            "Warning does not name the lock: {}".format(warnings),
        )

    def test_keeper_thread_survives_a_raising_lock_manager(self):
        lm = ExplodingLockManager()
        renewer = self._renewer("1", lm)
        renewer.start()

        with renewer.keep("poller.device.8", "node-Poller_4", 3):
            sleep(3.5)
            self.assertTrue(
                renewer._thread.is_alive(), "An exception killed the keeper thread"
            )
        self.assertGreaterEqual(
            len(lm.calls), 2, "Renewal was abandoned after the first exception"
        )

    def test_one_failing_lock_does_not_starve_the_others(self):
        lm = SelectivelyExplodingLockManager()
        renewer = self._renewer("1", lm)
        renewer.start()

        with renewer.keep("poller.device.bad", "node-Poller_5", 3):
            with renewer.keep("poller.device.good", "node-Poller_5", 3):
                sleep(3.5)

        self.assertGreaterEqual(
            len(lm.calls_for("poller.device.good")),
            2,
            "A throwing entry starved the healthy device in the same pass",
        )
        self.assertGreaterEqual(
            len(lm.calls_for("poller.device.bad")),
            2,
            "The throwing device was abandoned rather than retried",
        )

    def test_renew_pass_is_safe_while_locks_churn(self):
        # _renew_once() must snapshot the held map under the mutex. Iterating
        # it live raises "dictionary changed size during iteration" as soon as
        # a poll starts or finishes mid-pass -- and because _loop() catches
        # Exception, that would surface only as a log line, never a crash.
        # Driven directly here rather than via the keeper thread: on the 1s
        # _RESOLUTION cadence the window is too narrow to hit in a test.
        lm = RecordingLockManager()
        renewer = self._renewer("1", lm)  # deliberately not start()ed
        owner = "node-Poller_7"

        previous_interval = sys.getswitchinterval()
        sys.setswitchinterval(1e-6)
        self.addCleanup(sys.setswitchinterval, previous_interval)

        churn_done = threading.Event()
        churn_errors = []

        def churn(base):
            index = 0
            try:
                while not churn_done.is_set():
                    name = "poller.churn.{}.{}".format(base, index % 40)
                    with renewer.keep(name, owner, 3):
                        pass
                    index += 1
            except Exception as exc:  # pragma: no cover - failure path
                churn_errors.append(exc)

        churners = [threading.Thread(target=churn, args=(base,)) for base in range(8)]
        for churner in churners:
            churner.daemon = True
            churner.start()

        passes, deadline = 0, monotonic() + 3
        try:
            while monotonic() < deadline:
                renewer._renew_once()
                passes += 1
        finally:
            churn_done.set()
            for churner in churners:
                churner.join(5)

        for churner in churners:
            self.assertFalse(churner.is_alive(), "Churn thread did not finish")
        self.assertEqual([], churn_errors, "keep() raised while locks churned")
        self.assertGreater(passes, 100, "Too few renewal passes to prove anything")
        self.assertEqual({}, renewer._held, "Held map was not emptied on exit")

    def test_many_staggered_locks_renew_independently(self):
        lm = RecordingLockManager()
        logs = self._capture_logs()
        renewer = self._renewer("1", lm)
        renewer.start()

        # Eight devices, TTLs 3..10 => renewal intervals of 1.0s .. 3.33s
        ttls = {"poller.device.{}".format(ttl): ttl for ttl in range(3, 11)}
        owner = "node-Poller_6"

        with ExitStack() as stack:
            for name, ttl in sorted(ttls.items()):
                stack.enter_context(renewer.keep(name, owner, ttl))
            sleep(4.5)  # long enough for the 3.33s interval to come due

        self.assertEqual([], logs.messages(logging.ERROR), "A renewal pass failed")
        self.assertEqual({}, renewer._held, "Held map was not emptied on exit")

        for name, ttl in sorted(ttls.items()):
            calls = lm.calls_for(name)
            self.assertTrue(calls, "{} was never renewed".format(name))
            for call in calls:
                self.assertEqual(
                    (name, owner, ttl, True),
                    call,
                    "{} was renewed with another entry's parameters".format(name),
                )

        shortest = len(lm.calls_for("poller.device.3"))
        longest = len(lm.calls_for("poller.device.10"))
        self.assertGreater(
            shortest,
            longest,
            "Renewal cadence is not per-lock: ttl 3 renewed {} times, "
            "ttl 10 renewed {}".format(shortest, longest),
        )


if __name__ == "__main__":
    unittest.main()
