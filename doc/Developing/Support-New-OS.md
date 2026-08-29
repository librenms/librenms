This document has one section for each type of support. All the
examples add the OS `pulse`.

- [Adding the initial detection.](os/Initial-Detection.md)
- [Adding Memory and CPU information.](os/Mem-CPU-Information.md)
- [Adding Health / Sensor information.](os/Health-Information.md)
- [Adding Wireless Sensor information.](os/Wireless-Sensors.md)
- [Adding custom graphs.](os/Custom-Graphs.md)
- [Adding Unit tests (required).](os/Test-Units.md)
- [Optional Settings](os/Settings.md)

A script makes the deployment of a new OS faster. This script is in
pre-beta. It adds sensors in a basic form. It does not add state
sensors.

This example adds a new OS with the name `test-os`. It uses the
existing device ID 101. The type is network and the vendor is Cisco:

`./scripts/new-os.php -h 101 -o test-os -t network -v cisco`

The script then asks more questions. The script is in pre-beta and can
cause a problem. Report each problem on
[Discord](https://t.libren.ms/discord).
