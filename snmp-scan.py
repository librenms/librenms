#!/usr/bin/env python3
"""
Scan networks for snmp hosts and add them to LibreNMS

This script is deprecated and forwards arguments to `lnms scan:network`.
"""

import sys
import subprocess
import os

if __name__ == "__main__":
    print("This script is deprecated, use lnms scan:network")
    script_dir = os.path.dirname(os.path.realpath(__file__))
    os.chdir(script_dir)
    cmd = ["/usr/bin/env", "lnms", "scan:network"] + sys.argv[1:]
    sys.exit(subprocess.call(cmd))
