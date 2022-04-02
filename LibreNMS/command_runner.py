#! /usr/bin/env python
#  -*- coding: utf-8 -*-
#
# This file is part of command_runner module

"""
command_runner is a quick tool to launch commands from Python, get exit code
and output, and handle most errors that may happen

Versioning semantics:
    Major version: backward compatibility breaking changes
    Minor version: New functionality
    Patch version: Backwards compatible bug fixes

"""

__intname__ = "command_runner"
__author__ = "Orsiris de Jong"
__copyright__ = "Copyright (C) 2015-2021 Orsiris de Jong"
__licence__ = "BSD 3 Clause"
__version__ = "1.3.0"
__build__ = "2021100501"

import io
import os
import shlex
import subprocess
import sys
from datetime import datetime
from logging import getLogger
from time import sleep

try:
    import psutil
except ImportError:
    # Don't bother with an error since we need command_runner to work without dependencies
    pass
try:
    import signal
except ImportError:
    pass

# Python 2.7 compat fixes (queue was Queue)
try:
    import queue
except ImportError:
    import Queue as queue
import threading

# Python 2.7 compat fixes (missing typing and FileNotFoundError)
try:
    from typing import Union, Optional, List, Tuple, NoReturn, Any
except ImportError:
    pass
try:
    FileNotFoundError
except NameError:
    # pylint: disable=W0622 (redefined-builtin)
    FileNotFoundError = IOError
try:
    TimeoutExpired = subprocess.TimeoutExpired
except AttributeError:

    class TimeoutExpired(BaseException):
        """
        Basic redeclaration when subprocess.TimeoutExpired does not exist, python <= 3.3
        """

        def __init__(self, cmd, timeout, output=None, stderr=None):
            self.cmd = cmd
            self.timeout = timeout
            self.output = output
            self.stderr = stderr

        def __str__(self):
            return "Command '%s' timed out after %s seconds" % (self.cmd, self.timeout)

        @property
        def stdout(self):
            return self.output

        @stdout.setter
        def stdout(self, value):
            # There's no obvious reason to set this, but allow it anyway so
            # .stdout is a transparent alias for .output
            self.output = value


class KbdInterruptGetOutput(BaseException):
    """
    Make sure we get the current output when KeyboardInterrupt is made
    """

    def __init__(self, output):
        self._output = output

    @property
    def output(self):
        return self._output


logger = getLogger(__intname__)
PIPE = subprocess.PIPE
MIN_RESOLUTION = 0.05  # Minimal sleep time between polling, reduces CPU usage


def kill_childs_mod(
    pid=None,  # type: int
    itself=False,  # type: bool
    soft_kill=False,  # type: bool
):
    # type: (...) -> bool
    """
    Inline version of ofunctions.kill_childs that has no hard dependency on psutil

    Kills all childs of pid (current pid can be obtained with os.getpid())
    If no pid given current pid is taken
    Good idea when using multiprocessing, is to call with atexit.register(ofunctions.kill_childs, os.getpid(),)

    Beware: MS Windows does not maintain a process tree, so child dependencies are computed on the fly
    Knowing this, orphaned processes (where parent process died) cannot be found and killed this way

    Prefer using process.send_signal() in favor of process.kill() to avoid race conditions when PID was reused too fast

    :param pid: Which pid tree we'll kill
    :param itself: Should parent be killed too ?
    """
    sig = None

    ### BEGIN COMMAND_RUNNER MOD
    if "psutil" not in sys.modules:
        logger.error(
            "No psutil module present. Can only kill direct pids, not child subtree."
        )
    if "signal" not in sys.modules:
        logger.error(
            "No signal module present. Using direct psutil kill API which might have race conditions when PID is reused too fast."
        )
    else:
        """
        Extract from Python3 doc
        On Windows, signal() can only be called with SIGABRT, SIGFPE, SIGILL, SIGINT, SIGSEGV, SIGTERM, or SIGBREAK.
        A ValueError will be raised in any other case. Note that not all systems define the same set of signal names;
        an AttributeError will be raised if a signal name is not defined as SIG* module level constant.
        """
        try:
            if not soft_kill and hasattr(signal, "SIGKILL"):
                # Don't bother to make pylint go crazy on Windows
                # pylint: disable=E1101
                sig = signal.SIGKILL
            else:
                sig = signal.SIGTERM
        except NameError:
            sig = None
    ### END COMMAND_RUNNER MOD

    def _process_killer(
        process,  # type: Union[subprocess.Popen, psutil.Process]
        sig,  # type: signal.valid_signals
        soft_kill,  # type: bool
    ):
        # (...) -> None
        """
        Simple abstract process killer that works with signals in order to avoid reused PID race conditions
        and can prefers using terminate than kill
        """
        if sig:
            try:
                process.send_signal(sig)
            # psutil.NoSuchProcess might not be available, let's be broad
            # pylint: disable=W0703
            except Exception:
                pass
        else:
            if soft_kill:
                process.terminate()
            else:
                process.kill()

    try:
        current_process = psutil.Process(pid if pid is not None else os.getpid())
    # psutil.NoSuchProcess might not be available, let's be broad
    # pylint: disable=W0703
    except Exception:
        if itself:
            os.kill(
                pid, 15
            )  # 15 being signal.SIGTERM or SIGKILL depending on the platform
        return False

    for child in current_process.children(recursive=True):
        _process_killer(child, sig, soft_kill)

    if itself:
        _process_killer(current_process, sig, soft_kill)
    return True


def command_runner(
    command,  # type: Union[str, List[str]]
    valid_exit_codes=None,  # type: Optional[List[int]]
    timeout=3600,  # type: Optional[int]
    shell=False,  # type: bool
    encoding=None,  # type: Optional[str]
    stdout=None,  # type: Optional[Union[int, str]]
    stderr=None,  # type: Optional[Union[int, str]]
    windows_no_window=False,  # type: bool
    live_output=False,  # type: bool
    method="monitor",  # type: str
    **kwargs  # type: Any
):
    # type: (...) -> Tuple[Optional[int], str]
    """
    Unix & Windows compatible subprocess wrapper that handles output encoding and timeouts
    Newer Python check_output already handles encoding and timeouts, but this one is retro-compatible
    It is still recommended to set cp437 for windows and utf-8 for unix

    Also allows a list of various valid exit codes (ie no error when exit code = arbitrary int)

    command should be a list of strings, eg ['ping', '127.0.0.1', '-c 2']
    command can also be a single string, ex 'ping 127.0.0.1 -c 2' if shell=True or if os is Windows

    Accepts all of subprocess.popen arguments

    Whenever we can, we need to avoid shell=True in order to preserve better security
    Avoiding shell=True involves passing absolute paths to executables since we don't have shell PATH environment

    When no stdout option is given, we'll get output into the returned (exit_code, output) tuple
    When stdout = filename or stderr = filename, we'll write output to the given file

    live_output will poll the process for output and show it on screen (output may be non reliable, don't use it if
    your program depends on the commands' stdout output)

    windows_no_window will disable visible window (MS Windows platform only)

    Returns a tuple (exit_code, output)
    """

    # Choose default encoding when none set
    # cp437 encoding assures we catch most special characters from cmd.exe
    if not encoding:
        encoding = "cp437" if os.name == "nt" else "utf-8"

    # Fix when unix command was given as single string
    # This is more secure than setting shell=True
    if os.name == "posix" and shell is False and isinstance(command, str):
        command = shlex.split(command)

    # Set default values for kwargs
    errors = kwargs.pop(
        "errors", "backslashreplace"
    )  # Don't let encoding issues make you mad
    universal_newlines = kwargs.pop("universal_newlines", False)
    creationflags = kwargs.pop("creationflags", 0)
    # subprocess.CREATE_NO_WINDOW was added in Python 3.7 for Windows OS only
    if (
        windows_no_window
        and sys.version_info[0] >= 3
        and sys.version_info[1] >= 7
        and os.name == "nt"
    ):
        # Disable the following pylint error since the code also runs on nt platform, but
        # triggers an error on Unix
        # pylint: disable=E1101
        creationflags = creationflags | subprocess.CREATE_NO_WINDOW
    close_fds = kwargs.pop("close_fds", "posix" in sys.builtin_module_names)

    # Default buffer size. line buffer (1) is deprecated in Python 3.7+
    bufsize = kwargs.pop("bufsize", 16384)

    # Decide whether we write to output variable only (stdout=None), to output variable and stdout (stdout=PIPE)
    # or to output variable and to file (stdout='path/to/file')
    stdout_to_file = False
    if stdout is None:
        _stdout = PIPE
    elif isinstance(stdout, str):
        # We will send anything to file
        _stdout = open(stdout, "wb")
        stdout_to_file = True
    elif stdout is False:
        _stdout = subprocess.DEVNULL
    else:
        # We will send anything to given stdout pipe
        _stdout = stdout

    # The only situation where we don't add stderr to stdout is if a specific target file was given
    stderr_to_file = False
    if isinstance(stderr, str):
        _stderr = open(stderr, "wb")
        stderr_to_file = True
    elif stderr is False:
        _stderr = subprocess.DEVNULL
    else:
        _stderr = subprocess.STDOUT

    def to_encoding(
        process_output,  # type: Union[str, bytes]
        encoding,  # type: str
        errors,  # type: str
    ):
        # type: (...) -> str
        """
        Convert bytes output to string and handles conversion errors
        """
        # Compatibility for earlier Python versions where Popen has no 'encoding' nor 'errors' arguments
        if isinstance(process_output, bytes):
            try:
                process_output = process_output.decode(encoding, errors=errors)
            except TypeError:
                try:
                    # handle TypeError: don't know how to handle UnicodeDecodeError in error callback
                    process_output = process_output.decode(encoding, errors="ignore")
                except (ValueError, TypeError):
                    # What happens when str cannot be concatenated
                    logger.debug("Output cannot be captured {}".format(process_output))
        return process_output

    def _read_pipe(
        stream,  # type: io.StringIO
        output_queue,  # type: queue.Queue
    ):
        # type: (...) -> None
        """
        will read from subprocess.PIPE
        Must be threaded since readline() might be blocking on Windows GUI apps

        Partly based on https://stackoverflow.com/a/4896288/2635443
        """

        # WARNING: Depending on the stream type (binary or text), the sentinel character
        # needs to be of the same type, or the iterator won't have an end

        # We also need to check that stream has readline, in case we're writing to files instead of PIPE
        if hasattr(stream, "readline"):
            sentinel_char = "" if hasattr(stream, "encoding") else b""
            for line in iter(stream.readline, sentinel_char):
                output_queue.put(line)
            output_queue.put(None)
            stream.close()

    def _poll_process(
        process,  # type: Union[subprocess.Popen[str], subprocess.Popen]
        timeout,  # type: int
        encoding,  # type: str
        errors,  # type: str
    ):
        # type: (...) -> Tuple[Optional[int], str]
        """
        Process stdout/stderr output polling is only used in live output mode
        since it takes more resources than using communicate()

        Reads from process output pipe until:
        - Timeout is reached, in which case we'll terminate the process
        - Process ends by itself

        Returns an encoded string of the pipe output
        """

        begin_time = datetime.now()
        output = ""
        output_queue = queue.Queue()

        def __check_timeout(
            begin_time,  # type: datetime.timestamp
            timeout,  # type: int
        ):
            # type: (...) -> None
            """
            Simple subfunction to check whether timeout is reached
            Since we check this alot, we put it into a function
            """

            if timeout and (datetime.now() - begin_time).total_seconds() > timeout:
                kill_childs_mod(process.pid, itself=True, soft_kill=False)
                raise TimeoutExpired(process, timeout, output)

        try:
            read_thread = threading.Thread(
                target=_read_pipe, args=(process.stdout, output_queue)
            )
            read_thread.daemon = True  # thread dies with the program
            read_thread.start()

            while True:
                try:
                    line = output_queue.get(timeout=MIN_RESOLUTION)
                except queue.Empty:
                    __check_timeout(begin_time, timeout)
                else:
                    if line is None:
                        break
                    else:
                        line = to_encoding(line, encoding, errors)
                        if live_output:
                            sys.stdout.write(line)
                        output += line
                    __check_timeout(begin_time, timeout)

            # Make sure we wait for the process to terminate, even after
            # output_queue has finished sending data, so we catch the exit code
            while process.poll() is None:
                __check_timeout(begin_time, timeout)
            # Additional timeout check to make sure we don't return an exit code from processes
            # that were killed because of timeout
            __check_timeout(begin_time, timeout)
            exit_code = process.poll()
            return exit_code, output

        except KeyboardInterrupt:
            raise KbdInterruptGetOutput(output)

    def _timeout_check_thread(
        process,  # type: Union[subprocess.Popen[str], subprocess.Popen]
        timeout,  # type: int
        timeout_queue,  # type: queue.Queue
    ):
        # type: (...) -> None

        """
        Since elder python versions don't have timeout, we need to manually check the timeout for a process
        """

        begin_time = datetime.now()
        while True:
            if timeout and (datetime.now() - begin_time).total_seconds() > timeout:
                kill_childs_mod(process.pid, itself=True, soft_kill=False)
                timeout_queue.put(True)
                break
            if process.poll() is not None:
                break
            sleep(MIN_RESOLUTION)

    def _monitor_process(
        process,  # type: Union[subprocess.Popen[str], subprocess.Popen]
        timeout,  # type: int
        encoding,  # type: str
        errors,  # type: str
    ):
        # type: (...) -> Tuple[Optional[int], str]
        """
        Create a thread in order to enforce timeout
        Get stdout output and return it
        """

        # Shared mutable objects have proven to have race conditions with PyPy 3.7 (mutable object
        # is changed in thread, but outer monitor function has still old mutable object state)
        # Strangely, this happened only sometimes on github actions/ubuntu 20.04.3 & pypy 3.7
        # Let's create a queue to get the timeout thread response on a deterministic way
        timeout_queue = queue.Queue()
        is_timeout = False

        thread = threading.Thread(
            target=_timeout_check_thread,
            args=(process, timeout, timeout_queue),
        )
        thread.setDaemon(True)
        thread.start()

        process_output = None
        stdout = None

        try:
            # Don't use process.wait() since it may deadlock on old Python versions
            # Also it won't allow communicate() to get incomplete output on timeouts
            while process.poll() is None:
                sleep(MIN_RESOLUTION)
                try:
                    is_timeout = timeout_queue.get_nowait()
                except queue.Empty:
                    pass
                else:
                    break
                # We still need to use process.communicate() in this loop so we don't get stuck
                # with poll() is not None even after process is finished
                if _stdout is not False:
                    try:
                        stdout, _ = process.communicate()
                    # ValueError is raised on closed IO file
                    except (TimeoutExpired, ValueError):
                        pass
            exit_code = process.poll()

            if _stdout is not False:
                try:
                    stdout, _ = process.communicate()
                except (TimeoutExpired, ValueError):
                    pass
            process_output = to_encoding(stdout, encoding, errors)

            # On PyPy 3.7 only, we can have a race condition where we try to read the queue before
            # the thread could write to it, failing to register a timeout.
            # This workaround prevents reading the queue while the thread is still alive
            while thread.is_alive():
                sleep(MIN_RESOLUTION)

            try:
                is_timeout = timeout_queue.get_nowait()
            except queue.Empty:
                pass
            if is_timeout:
                raise TimeoutExpired(process, timeout, process_output)
            return exit_code, process_output
        except KeyboardInterrupt:
            raise KbdInterruptGetOutput(process_output)

    try:
        # Finally, we won't use encoding & errors arguments for Popen
        # since it would defeat the idea of binary pipe reading in live mode

        # Python >= 3.3 has SubProcessError(TimeoutExpired) class
        # Python >= 3.6 has encoding & error arguments
        # universal_newlines=True makes netstat command fail under windows
        # timeout does not work under Python 2.7 with subprocess32 < 3.5
        # decoder may be cp437 or unicode_escape for dos commands or utf-8 for powershell
        # Disabling pylint error for the same reason as above
        # pylint: disable=E1123
        if sys.version_info >= (3, 6):
            process = subprocess.Popen(
                command,
                stdout=_stdout,
                stderr=_stderr,
                shell=shell,
                universal_newlines=universal_newlines,
                encoding=encoding,
                errors=errors,
                creationflags=creationflags,
                bufsize=bufsize,  # 1 = line buffered
                close_fds=close_fds,
                **kwargs
            )
        else:
            process = subprocess.Popen(
                command,
                stdout=_stdout,
                stderr=_stderr,
                shell=shell,
                universal_newlines=universal_newlines,
                creationflags=creationflags,
                bufsize=bufsize,
                close_fds=close_fds,
                **kwargs
            )

        try:
            if method == "poller" or live_output and _stdout is not False:
                exit_code, output = _poll_process(process, timeout, encoding, errors)
            else:
                exit_code, output = _monitor_process(process, timeout, encoding, errors)
        except KbdInterruptGetOutput as exc:
            exit_code = -252
            output = "KeyboardInterrupted. Partial output\n{}".format(exc.output)
            try:
                kill_childs_mod(process.pid, itself=True, soft_kill=False)
            except AttributeError:
                pass
            if stdout_to_file:
                _stdout.write(output.encode(encoding, errors=errors))

        logger.debug(
            'Command "{}" returned with exit code "{}". Command output was:'.format(
                command, exit_code
            )
        )
    except subprocess.CalledProcessError as exc:
        exit_code = exc.returncode
        try:
            output = exc.output
        except AttributeError:
            output = "command_runner: Could not obtain output from command."
        if exit_code in valid_exit_codes if valid_exit_codes is not None else [0]:
            logger.debug(
                'Command "{}" returned with exit code "{}". Command output was:'.format(
                    command, exit_code
                )
            )
        logger.error(
            'Command "{}" failed with exit code "{}". Command output was:'.format(
                command, exc.returncode
            )
        )
        logger.error(output)
    except FileNotFoundError as exc:
        logger.error('Command "{}" failed, file not found: {}'.format(command, exc))
        exit_code, output = -253, exc.__str__()
    # On python 2.7, OSError is also raised when file is not found (no FileNotFoundError)
    # pylint: disable=W0705 (duplicate-except)
    except (OSError, IOError) as exc:
        logger.error('Command "{}" failed because of OS: {}'.format(command, exc))
        exit_code, output = -253, exc.__str__()
    except TimeoutExpired as exc:
        message = 'Timeout {} seconds expired for command "{}" execution. Original output was: {}'.format(
            timeout, command, exc.output
        )
        logger.error(message)
        if stdout_to_file:
            _stdout.write(message.encode(encoding, errors=errors))
        exit_code, output = (
            -254,
            'Timeout of {} seconds expired for command "{}" execution. Original output was: {}'.format(
                timeout, command, exc.output
            ),
        )
    # We need to be able to catch a broad exception
    # pylint: disable=W0703
    except Exception as exc:
        logger.error(
            'Command "{}" failed for unknown reasons: {}'.format(command, exc),
            exc_info=True,
        )
        logger.debug("Error:", exc_info=True)
        exit_code, output = -255, exc.__str__()
    finally:
        if stdout_to_file:
            _stdout.close()
        if stderr_to_file:
            _stderr.close()

    logger.debug(output)

    return exit_code, output


def deferred_command(command, defer_time=300):
    # type: (str, int) -> None
    """
    This is basically an ugly hack to launch commands which are detached from parent process
    Especially useful to launch an auto update/deletion of a running executable after a given amount of
    seconds after it finished
    """
    # Use ping as a standard timer in shell since it's present on virtually *any* system
    if os.name == "nt":
        deferrer = "ping 127.0.0.1 -n {} > NUL & ".format(defer_time)
    else:
        deferrer = "ping 127.0.0.1 -c {} > /dev/null && ".format(defer_time)

    # We'll create a independent shell process that will not be attached to any stdio interface
    # Our command shall be a single string since shell=True
    subprocess.Popen(
        deferrer + command,
        shell=True,
        stdin=None,
        stdout=None,
        stderr=None,
        close_fds=True,
    )
