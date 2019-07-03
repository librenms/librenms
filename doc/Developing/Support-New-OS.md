source: Developing/Support-New-OS.md
path: blob/master/doc/

This document is broken down into the relevant sections depending on
what support you are adding. During all of these examples we will be
using the OS of `pulse` as the example OS we will add.

- [Adding the initial detection.](os/Initial-Detection.md)
- [Adding Memory and CPU information.](os/Mem-CPU-Information.md)
- [Adding Health / Sensor information.](os/Health-Information.md)
- [Adding Wireless Sensor information.](os/Wireless-Sensors.md)
- [Adding custom graphs.](os/Custom-Graphs.md)
- [Adding Unit tests (required).](os/Test-Units.md)
- [Optional Settings](os/Settings.md)

We currently have a script in pre-beta stages that can help speed up
the process of deploying a new OS. It has support for add sensors in a
basic form (except state sensors).

In this example, we will add a new OS called test-os using the device
ID 101 that has already been added. It will be of the type network and
belongs to the vendor, Cisco:

`./scripts/new-os.php -h 101 -o test-os -t network -v cisco`

The process will then step you through the process with some more
questions. Please be warned, this is  currently pre-beta and may cause
some issues. Please let us know of any on [Discord](https://t.libren.ms/discord).
