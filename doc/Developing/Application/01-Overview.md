---
title: Application Development Overview
description: Introduction and reading guide for developing LibreNMS applications using agent or JSON payloads.
tags:
  - developing
  - applications
---

# Application Development Overview

This chapter set explains how to develop LibreNMS applications that receive data from the LibreNMS agent or JSON app endpoints.

The recommended path for new application code is to extend `LibreNMS\Agent\Application`. This gives one consistent model for payload fetching, discovery, polling, sensor updates, RRD writes, app metrics, and event logging.

## Recommended reading order

| Goal | Read |
| --- | --- |
| Understand the complete flow | `02-Application-Developing.md` |
| Understand discovery vs polling | `10-Collecting-info.md` |
| Create app-based sensors | `11-App-Based-Sensors.md` |
| Store app status and metrics | `12-Database-storing.md` |
| See a complete handler | `13-Common-Functions-Example.md` |
| Decide whether to hide or avoid sensors | `14-Hiding-sensors.md` |
| Add dedicated tables or schema changes | `15-Migrations.md` |
| Render application data in the UI | `20-displaying-info.md` |
| Add an Apps tab page | `21-App-Pages.md` |
| Add a Device Overview panel | `23-Device-Overview-Panel.md` |
| Build the monitored-host SNMP extend script | `30-Extension-Developing.md` |

## Scope

These pages cover the LibreNMS side of application development:

- application handler registration
- discovery and polling
- app-based sensors
- application metrics
- RRD storage
- app pages and overview panels
- migrations only when a custom table is required

Host-side SNMP extend development is covered separately in `30-Extension-Developing.md`.

## Golden path

For a new JSON or agent-based app, use this flow:

1. Define the host-side JSON contract.
2. Register a handler in `resources/definitions/agent/unix.yaml`.
3. Create a class extending `LibreNMS\Agent\Application`.
4. Implement `discover()` if the app creates sensors.
5. Implement `poll()` for 5-minute value updates.
6. Use `update_application()` for app-level status and metrics.
7. Add RRDs, app pages, or overview panels only when the UI needs them.

## Related reference

- [Sensor State Support](../Sensor-State-Support.md)
- [RRD](../RRD.md)
- [Developing SNMP Extensions](30-Extension-Developing.md)
