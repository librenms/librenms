#! /usr/bin/env python3
import os
import sys
import pkg_resources
from pkg_resources import DistributionNotFound, VersionConflict

args = sys.argv

# verbose flag
verbose = '-v' in args

requirements = [
    'PyMySQL'
]

try:
    pkg_resources.require(requirements)
except DistributionNotFound as req:
    if verbose:
        print(req)
    exit(1)
except VersionConflict as req:
    if verbose:
        print(req)
    exit(2)
exit(0)
