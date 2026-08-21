# NMS-WHITE Architecture

## 1. Purpose

NMS-WHITE is a LibreNMS-based Network Management System with custom branding, dashboard, API, multi-tenancy, Docker deployment, and native installation support.

Core principle:

- **LibreNMS = monitoring engine**
- **NMS-WHITE = product/application layer**

NMS-WHITE should add product-specific functionality without unnecessarily modifying LibreNMS core.

## 2. High-Level Architecture

```text
                    NMS-WHITE
                        |
          +-------------+-------------+
          |                           |
      NMS-WHITE UI               NMS-WHITE API
          |                           |
          +-------------+-------------+
                        |
                  LibreNMS Core
                        |
       +----------------+----------------+
       |                |                |
      SNMP            Poller          Alerting
       |                |                |
       +----------------+----------------+
                        |
                 MariaDB / Redis
                        |
          +-------------+-------------+
          |             |             |
        Switch          AP          Router
3. Repository Strategy

NMS-WHITE maintains a fork of LibreNMS so LibreNMS remains an explicitly tracked upstream project.

origin
  -> RND-NoriTech/NMS-WHITE-V2


upstream
  -> librenms/librenms

The repository must preserve the ability to fetch and integrate upstream LibreNMS changes.

The current upstream branch is master.

4. Branch Strategy
master
  |
  +-- stable / upstream-aligned baseline


develop
  |
  +-- feature/*

The current Phase 0 work is performed on:

feature/phase-0-foundation

Normal development changes should not be made directly on master.

The master branch is protected on GitHub.

5. Upstream Synchronization

The intended synchronization flow is:

LibreNMS upstream/master
        |
        | git fetch upstream
        v
     master
        |
        v
     develop
        |
        +-- feature/*

Upstream synchronization must be performed deliberately and tested before changes are promoted into the stable branch.

NMS-WHITE custom functionality should remain separated from upstream changes wherever practical.

6. Application Boundary

NMS-WHITE is the product/application layer around LibreNMS.

Preferred extension areas include:

NMS-WHITE application modules
Plugins or extension mechanisms
Custom dashboard components
Custom API functionality
Branding assets
Reports
Tenant functionality
Product-specific settings

LibreNMS core should only be modified when there is no reasonable extension point.

This reduces upstream merge conflicts and makes future LibreNMS upgrades easier.

7. Database Strategy

LibreNMS remains responsible for its existing monitoring data model.

NMS-WHITE-specific application data should use separate tables with an nms_ naming convention where new tables are required.

Examples planned for later phases:

nms_tenants
nms_tenant_devices

NMS-WHITE should avoid unnecessary modifications to existing LibreNMS tables.

8. Deployment Strategy

NMS-WHITE uses a single application codebase.

Docker and native installation must not become separate application implementations.

              NMS-WHITE SOURCE
                     |
             +-------+-------+
             |               |
          Native           Docker
        installer       compose/images
             |               |
             +-------+-------+
                     |
              SAME APPLICATION

Docker is the primary development and initial deployment target.

Native installation is a later phase and should consume the same application source.

9. Docker-First Development

Phase 1 will establish a clean, production-like LibreNMS environment using Docker before substantial NMS-WHITE customization is introduced.

Initial target:

Ubuntu
  |
Docker
  |
LibreNMS
  |
First device
  |
SNMP
  |
Polling
  |
Graphs
  |
Alerts

The monitoring foundation should be proven before building higher-level NMS-WHITE functionality.

10. Licensing and GPLv3 Compliance

NMS-WHITE is based on LibreNMS and must preserve required LibreNMS licensing, copyright, and attribution information.

NMS-WHITE must not globally replace LibreNMS license notices, copyright notices, or required third-party attribution merely for branding purposes.

GPLv3 compliance requirements must be documented and reviewed before commercial distribution.

Legal review should be obtained for the final commercial licensing and distribution structure.

11. Architecture Principles
LibreNMS remains the monitoring engine.
NMS-WHITE is the product/application layer.
Prefer extension mechanisms over LibreNMS core modifications.
Keep NMS-WHITE-specific database data separate from LibreNMS monitoring data where practical.
Maintain a single application codebase for Docker and native deployment.
Use Docker as the primary development and initial deployment target.
Keep LibreNMS upstream tracking explicit and reproducible.
Protect the stable master branch.
Develop changes through feature branches and develop.
Preserve GPLv3 licensing, copyright, and attribution requirements.
12. Phase 0 Status

This document records the initial architecture and repository strategy for Phase 0.
