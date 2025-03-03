#! /usr/bin/env python3
import pkg_resources
import sys
from pkg_resources import DistributionNotFound, VersionConflict

args = sys.argv

# verbose flag
verbose = "-v" in args

requirements = ["PyMySQL"]

try:
    pkg_resources.require(requirements)
except DistributionNotFound as req:
    if verbose:
        print(req)
    sys.exit(1)
except VersionConflict as req:
    if verbose:
        print(req)
    sys.exit(2)
sys.exit(0)
