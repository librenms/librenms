# Application Development Overview

This chapter set organizes LibreNMS application development docs into a clear, task-oriented flow.

## Scope

Application pollers receive data via the LibreNMS agent or JSON app endpoints.

The recommended approach is to extend `LibreNMS\Agent\Application`, which provides consistent patterns for sensor discovery, polling, RRD writes, and event logging.

Using this pattern, discovery carries most structure-building complexity, so the 5-minute poll path can stay small and focused instead of repeatedly running full discovery-style code.

Application panels appear in the left column of the **Device Overview** page, below ports and transceivers. Each application that wants a panel ships two files: a PHP glue file that guards and passes data, and a Blade template that renders the HTML.

## Related Reference

- [Sensor State Support](../Sensor-State-Support.md)
- [RRD](../RRD.md)
- [Extension Developing](30-Extension-Developing.md)
