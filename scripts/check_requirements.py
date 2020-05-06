#! /usr/bin/env python3
import os
import pkg_resources
from pkg_resources import DistributionNotFound, VersionConflict

target = os.path.realpath(os.path.dirname(__file__) + '/../requirements.txt')

with open(target, 'r') as file:
    requirements = file.read().rstrip().split("\n")
    try:
        pkg_resources.require(requirements)
    except DistributionNotFound:
        exit(1)
    except VersionConflict:
        exit(2)
    exit(0)

exit(3)
