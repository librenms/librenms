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
__version__ = "1.0.0-dev"
__build__ = "2021083101"

import os
import io
import shlex
import subprocess
import sys
from time import sleep
from datetime import datetime
from logging import getLogger

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


def command_runner(
    command,  # type: Union[str, List[str]]
    valid_exit_codes=None,  # type: Optional[List[int]]
    timeout=3600,  # type: Optional[int]
    shell=False,  # type: bool
    encoding=None,  # type: str
    stdout=None,  # type: Union[int, str]
    stderr=None,  # type: Union[int, str]
    windows_no_window=False,  # type: bool
    live_output=False,  # type: bool
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

    # Decide whether we write to output variable only (stdout=None), to output variable and stdout (stdout=PIPE)
    # or to output variable and to file (stdout='path/to/file')
    if stdout is None:
        _stdout = PIPE
        stdout_to_file = False
    elif isinstance(stdout, str):
        # We will send anything to file
        _stdout = open(stdout, "wb")
        stdout_to_file = True
    else:
        # We will send anything to given stdout pipe
        _stdout = stdout
        stdout_to_file = False

    # The only situation where we don't add stderr to stdout is if a specific target file was given
    if isinstance(stderr, str):
        _stderr = open(stderr, "wb")
        stderr_to_file = True
    else:
        _stderr = subprocess.STDOUT
        stderr_to_file = False

    def _windows_child_kill(
        pid,  # type: int
    ):
        # type: (...) -> None
        """
        windows does not have child process trees
        So in order to deal with child process kills, we need to use a system tool here
        """
        dev_null = open(os.devnull, "w")
        subprocess.call(
            ["taskkill", "/F", "/T", "/PID", str(pid)],
            stdin=dev_null,
            stdout=dev_null,
            stderr=dev_null,
        )

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

    def kill(
        process,  # type: Union[subprocess.Popen[str], subprocess.Popen]
    ):
        # type: (...) -> None
        """
        OS agnostic process kill function
        """

        # Try to terminate nicely before killing the process
        if os.name == "nt":
            _windows_child_kill(process.pid)
        process.terminate()
        # Let the process terminate itself before trying to kill it not nicely
        # Under windows, terminate() and kill() are equivalent
        if process.poll() is None:
            process.kill()

    def _read_pipe(
        stream,  # type: io.BinaryIO
        output_queue,  # type: Optional[queue.Queue]
    ):
        # type: (...) -> None
        """
        will read from subprocess.PIPE
        Must be threaded since readline() might be blocking on Windows GUI apps
        """
        int_queue = queue.Queue()

        def stream_reader():
            while True:
                buffer = stream.read1(8192)
                if len(buffer) > 0:
                    int_queue.put(buffer)
                else:
                    int_queue.put(None)
                    return

        def queue_transfer():
            active = True
            while active:
                output = int_queue.get()
                try:
                    while True:
                        partial_output = int_queue.get(timeout=0.005)
                        if partial_output is None:
                            active = False
                            break
                        output += partial_output
                except queue.Empty:
                    pass
                output_queue.put(output)

        for function in [stream_reader, queue_transfer]:
            thread = threading.Thread(target=function)
            thread.setDaemon(True)
            thread.start()

    def _poll_process(
        process,  # type: Union[subprocess.Popen[str], subprocess.Popen]
        timeout,  # type: int
        encoding,  # type: str
        errors,  # type: str
    ):
        # type: (...) -> Tuple[Optional[int], str]
        """
        Process stdout/stderr output polling is only used in live output mode
        because it can be unreliable

        Reads from process output pipe until:
        - Timeout is reached, in which case we'll terminate the process
        - Process ends by itself

        Returns an encoded string of the pipe output
        """

        begin_time = datetime.now()

        output = ""
        output_queue = queue.Queue()
        with io.open(process.stdout.fileno(), "rb", closefd=False) as stdout:
            _read_pipe(stdout, output_queue)

        try:
            while process.poll() is None:
                try:
                    stream_output = output_queue.get(timeout=1.0)
                    stream_output = to_encoding(stream_output, encoding, errors)
                    output += stream_output
                    if stream_output and live_output:
                        sys.stdout.write(stream_output)

                    if (
                        timeout
                        and (datetime.now() - begin_time).total_seconds() > timeout
                    ):
                        kill(process)
                        raise TimeoutExpired(process, timeout, output)

                except queue.Empty:
                    pass
            return process.poll(), output
        except KeyboardInterrupt:
            raise KbdInterruptGetOutput(output)

    def _timeout_check(
        process,  # type: Union[subprocess.Popen[str], subprocess.Popen]
        timeout,  # type: int
        timeout_dict,  # type: dict
    ):
        # type: (...) -> None

        """
        Since elder python versions don't have timeout, we need to manually check the timeout for a process
        """

        begin_time = datetime.now()
        while True:
            if timeout and (datetime.now() - begin_time).total_seconds() > timeout:
                timeout_dict["is_timeout"] = True
                kill(process)
                break
            if process.poll() is not None:
                break
            sleep(0.1)

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

        # Let's create a mutable object since it will be shared with a thread
        timeout_dict = {"is_timeout": False}

        thread = threading.Thread(
            target=_timeout_check,
            args=(process, timeout, timeout_dict),
        )
        thread.setDaemon(True)
        thread.start()

        process_output = None
        stdout = None

        try:
            # Don't use process.wait() since it may deadlock on old Python versions
            # Also it won't allow communicate() to get incomplete output on timeouts
            while process.poll() is None:
                sleep(0.1)
                try:
                    stdout, _ = process.communicate()
                # ValueError is raised on closed IO file
                except (TimeoutExpired, ValueError):
                    pass

            exit_code = process.poll()
            try:
                stdout, _ = process.communicate()
            except (TimeoutExpired, ValueError):
                pass
            process_output = to_encoding(stdout, encoding, errors)

            if timeout_dict["is_timeout"]:
                raise TimeoutExpired(process, timeout, process_output)

            return exit_code, process_output
        except KeyboardInterrupt:
            raise KbdInterruptGetOutput(process_output)

    try:
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
                bufsize=1,  # 1 = line buffered
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
                bufsize=1,
                close_fds=close_fds,
                **kwargs
            )

        try:
            if live_output:
                exit_code, output = _poll_process(process, timeout, encoding, errors)
            else:
                exit_code, output = _monitor_process(process, timeout, encoding, errors)
        except KbdInterruptGetOutput as exc:
            exit_code = -252
            output = "KeyboardInterrupted. Partial output\n{}".format(exc.output)
            try:
                process.kill()
                if os.name == "nt":
                    _windows_child_kill(process.pid)
                process.kill()
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
