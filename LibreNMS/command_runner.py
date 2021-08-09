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
__version__ = "0.7.0"
__build__ = "2021080201"

import os
import shlex
import subprocess
import sys
from datetime import datetime
from logging import getLogger
from time import sleep

# Python 2.7 compat fixes (queue was Queue)
try:
    import Queue
except ImportError:
    import queue as Queue
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

        pass


logger = getLogger(__intname__)
PIPE = subprocess.PIPE


def thread_with_result_queue(fn):
    """
    Python 2.7 compatible implementation of concurrent.futures
    Executes decorated function as thread and adds a queue for result communication
    """

    def wrapped_fn(queue, *args, **kwargs):
        result = fn(*args, **kwargs)
        queue.put(result)

    def wrapper(*args, **kwargs):
        queue = Queue.Queue()

        child_thread = threading.Thread(
            target=wrapped_fn, args=(queue,) + args, kwargs=kwargs
        )
        child_thread.start()
        child_thread.result_queue = queue
        return child_thread

    return wrapper


def command_runner(
    command,  # type: Union[str, List[str]]
    valid_exit_codes=None,  # type: Optional[List[int]]
    timeout=1800,  # type: Optional[int]
    shell=False,  # type: bool
    encoding=None,  # type: str
    stdout=None,  # type: Union[int, str]
    stderr=None,  # type: Union[int, str]
    windows_no_window=False,  # type: bool
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

    Whenever we can, we need to avoid shell=True in order to preseve better security

    When no stdout option is given, we'll get output into the returned tuple
    When stdout = PIPE or subprocess.PIPE, output is also displayed on the fly on stdout
    When stdout = filename or stderr = filename, we'll write output to the given file

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

    # Decide whether we write to output variable only (stdout=None), to output variable and stdout (stdout=PIPE)
    # or to output variable and to file (stdout='path/to/file')
    if stdout is None:
        _stdout = subprocess.PIPE
        stdout_to_file = False
        live_output = False
    elif isinstance(stdout, str):
        # We will send anything to file
        _stdout = open(stdout, "wb")
        stdout_to_file = True
        live_output = False
    else:
        # We will send anything to given stdout pipe
        _stdout = stdout
        stdout_to_file = False
        live_output = True

    # The only situation where we don't add stderr to stdout is if a specific target file was given
    if isinstance(stderr, str):
        _stderr = open(stderr, "wb")
        stderr_to_file = True
    else:
        _stderr = subprocess.STDOUT
        stderr_to_file = False

    @thread_with_result_queue
    def _pipe_read(pipe, read_timeout, encoding, errors):
        # type: (subprocess.PIPE, Union[int, float], str, str) -> str
        """
        Read pipe will read from subprocess.PIPE for at most 1 second
        """
        pipe_output = ""
        begin_time = datetime.now()
        try:
            while True:
                current_output = pipe.readline()
                # Compatibility for earlier Python versions where Popen has no 'encoding' nor 'errors' arguments
                if isinstance(current_output, bytes):
                    try:
                        current_output = current_output.decode(encoding, errors=errors)
                    except TypeError:
                        # handle TypeError: don't know how to handle UnicodeDecodeError in error callback
                        current_output = current_output.decode(
                            encoding, errors="ignore"
                        )
                pipe_output += current_output
                if live_output:
                    sys.stdout.write(current_output)
                if (
                    len(current_output) <= 0
                    or (datetime.now() - begin_time).total_seconds() > read_timeout
                ):
                    break
        # Pipe may not have readline() anymore when process gets killed
        except AttributeError:
            pass
        return pipe_output

    def _poll_process(
        process,  # type: Union[subprocess.Popen[str], subprocess.Popen]
        timeout,  # type: int
        encoding,  # type: str
        errors,  # type: str
    ):
        # type: (...) -> Tuple[Optional[int], str]
        """
        Reads from process output pipe until:
        - Timeout is reached, in which case we'll terminate the process
        - Process ends by itself

        Returns an encoded string of the pipe output
        """
        output = ""
        begin_time = datetime.now()
        timeout_reached = False
        while process.poll() is None:
            child = _pipe_read(
                process.stdout, read_timeout=1, encoding=encoding, errors=errors
            )
            try:
                output += child.result_queue.get(timeout=1)
            except Queue.Empty:
                pass
            except ValueError:
                # What happens when str cannot be concatenated
                pass
            if timeout:
                if (datetime.now() - begin_time).total_seconds() > timeout:
                    # Try to terminate nicely before killing the process
                    process.terminate()
                    # Let the process terminate itself before trying to kill it not nicely
                    # Under windows, terminate() and kill() are equivalent
                    sleep(0.5)
                    if process.poll() is None:
                        process.kill()
                    timeout_reached = True

        # Get remaining output from process after a grace period
        sleep(0.5)

        exit_code = process.poll()
        if timeout:
            # Let's wait a second more so we may get the remaining queue content after process is to be ended
            final_read_timeout = max(
                1, timeout - (datetime.now() - begin_time).total_seconds(), 1
            )
        else:
            final_read_timeout = 1
        child = _pipe_read(
            process.stdout,
            read_timeout=final_read_timeout,
            encoding=encoding,
            errors=errors,
        )
        try:
            output += child.result_queue.get(timeout=final_read_timeout)
        except Queue.Empty:
            pass
        except ValueError:
            # What happens when str cannot be concatenated
            pass
        if timeout_reached:
            raise TimeoutExpired(process, timeout)
        return exit_code, output

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
                **kwargs
            )

        try:
            exit_code, output = _poll_process(process, timeout, encoding, errors)
        except KeyboardInterrupt:
            exit_code = -252
            output = "KeyboardInterrupted"
            try:
                process.kill()
            except AttributeError:
                pass

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
    # OSError can also catch FileNotFoundErrors
    # pylint: disable=W0705 (duplicate-except)
    except (OSError, IOError) as exc:
        logger.error('Command "{}" failed because of OS: {}'.format(command, exc))
        exit_code, output = -253, exc.__str__()
    except TimeoutExpired:
        message = 'Timeout {} seconds expired for command "{}" execution.'.format(
            timeout, command
        )
        logger.error(message)
        if stdout_to_file:
            _stdout.write(message.encode(encoding, errors=errors))
        exit_code, output = -254, "Timeout of {} seconds expired.".format(timeout)
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
    # type: (str, int) -> NoReturn
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
