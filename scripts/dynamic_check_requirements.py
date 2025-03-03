#! /usr/bin/env python3
#  -*- coding: utf-8 -*-
#
# This file is part of LibreNMS

__author__ = "Orsiris de Jong"
__copyright__ = "Copyright (C) 2021 LibreNMS"

import os
import sys

import pkg_resources

args = sys.argv

# verbose flag
verbose = "-v" in args


def _read_file(filename):
    if sys.version_info[0] > 2:
        with open(filename, "r", encoding="utf-8") as file_handle:
            return file_handle.read()
    else:
        # With python 2.7, open has no encoding parameter, resulting in TypeError
        # Fix with io.open (slow but works)
        from io import open as io_open

        with io_open(filename, "r", encoding="utf-8") as file_handle:
            return file_handle.read()


def parse_requirements(filename):
    """
    There is a parse_requirements function in pip but it keeps changing import path
    Let's build a simple one
    """
    try:
        requirements_txt = _read_file(filename)
        install_requires = [
            str(requirement)
            for requirement in pkg_resources.parse_requirements(requirements_txt)
        ]
        return install_requires
    except OSError:
        print(
            'WARNING: No requirements.txt file found as "{}". Please check path or create an empty one'.format(
                filename
            )
        )
        sys.exit(3)


base_dir = os.path.abspath(os.path.dirname(os.path.dirname(__file__)))
requirements = parse_requirements(os.path.join(base_dir, "requirements.txt"))
if verbose:
    print("Required packages:", requirements)

try:
    pkg_resources.require(requirements)
except pkg_resources.DistributionNotFound as req:
    if verbose:
        print("Package not found: {}".format(req))
    sys.exit(1)
except pkg_resources.VersionConflict as req:
    if verbose:
        print("Required version not satisfied: {}".format(req))
    sys.exit(2)
sys.exit(0)
