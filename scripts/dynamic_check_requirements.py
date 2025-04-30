#! /usr/bin/env python3
# -*- coding: utf-8 -*-
#
# This file is part of LibreNMS

__author__    = "Orsiris de Jong"
__copyright__ = "Copyright (C) 2021 LibreNMS"

import os
import sys
from typing import List

# Import metadata API (stdlib on Py3.8+, backport on older)
try:
    from importlib.metadata import version, PackageNotFoundError
except ImportError:
    from importlib_metadata import version, PackageNotFoundError  # type: ignore

# Try to use packaging for parsing; if unavailable, fallback to pkg_resources
try:
    from packaging.requirements import Requirement
    from packaging.specifiers   import SpecifierSet
    USE_PACKAGING = True
except ImportError:
    try:
        import pkg_resources
        USE_PACKAGING = False
    except ImportError:
        print(
            "ERROR: This script requires either the 'packaging' library or setuptools (for pkg_resources).",
            file=sys.stderr,
        )
        print(
            "Please install one of them in your Python environment:\n"
            "  pip install --user packaging\n"
            "or\n"
            "  pip install --user setuptools",
            file=sys.stderr,
        )
        sys.exit(3)


def _read_file(filename: str) -> str:
    """
    Read a text file using UTF-8 encoding.
    """
    with open(filename, "r", encoding="utf-8") as fh:
        return fh.read()


def parse_requirements(filename: str) -> List[str]:
    """
    Read a requirements.txt and return a list of non-blank,
    non-comment lines.
    """
    try:
        text = _read_file(filename)
    except OSError:
        print(f'WARNING: No requirements.txt found at "{filename}"', file=sys.stderr)
        sys.exit(3)

    lines: List[str] = []
    for raw in text.splitlines():
        line = raw.strip()
        if not line or line.startswith("#"):
            continue
        lines.append(line)
    return lines


def check(requirements: List[str], verbose: bool) -> None:
    """
    Verify that each requirement is installed and satisfies its specifier.
    If packaging is available, do manual checks; otherwise delegate to pkg_resources.
    """
    if USE_PACKAGING:
        for req_str in requirements:
            req = Requirement(req_str)
            name = req.name
            spec: SpecifierSet = req.specifier

            try:
                installed = version(name)
            except PackageNotFoundError:
                if verbose:
                    print(f"Package not found: {name}")
                sys.exit(1)

            if spec and not spec.contains(installed, prereleases=True):
                if verbose:
                    print(f"Required version not satisfied: {name}{spec} (installed {installed})")
                sys.exit(2)

        sys.exit(0)

    else:
        try:
            pkg_resources.require(requirements)
            sys.exit(0)
        except pkg_resources.DistributionNotFound as e:
            if verbose:
                print(f"Package not found: {e}")
            sys.exit(1)
        except pkg_resources.VersionConflict as e:
            if verbose:
                print(f"Required version not satisfied: {e}")
            sys.exit(2)


if __name__ == "__main__":
    args = sys.argv[1:]
    verbose = "-v" in args or "--verbose" in args

    base_dir = os.path.abspath(os.path.dirname(os.path.dirname(__file__)))
    req_file = os.path.join(base_dir, "requirements.txt")
    requirements = parse_requirements(req_file)

    if verbose:
        print("Required packages:", requirements)

    check(requirements, verbose)

