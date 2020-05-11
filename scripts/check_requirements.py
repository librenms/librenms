#! /usr/bin/env python3
import os
import sys
import pkg_resources
from pkg_resources import DistributionNotFound, VersionConflict

args = sys.argv

# verbose flag
verbose = '-v' in args

target = os.path.realpath(os.path.dirname(__file__) + '/../requirements.txt')

with open(target, 'r') as file:
    requirements = file.read().rstrip().split("\n")
    requirements.reverse()  # reverse so the most important ones show first
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

exit(3)
