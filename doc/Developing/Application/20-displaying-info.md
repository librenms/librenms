---
title: 2. Displaying Info
description: Display-side patterns for presenting LibreNMS application data in the UI.
tags:
  - developing
  - applications
---

# 2. Displaying Info

This section covers display-side patterns for LibreNMS application data.

Application data can appear in two main places:

| UI location | Use when |
| --- | --- |
| Device Apps tab | The user needs graphs, tables, drill-down views, or per-instance pages |
| Device Overview panel | The user needs a compact status summary on the main device page |

## Sub-chapters

- `21-App-Pages.md` - app tab patterns: flat graphs, tabbed sections, per-instance views, tables, and graph selectors.
- `22-Graph-Auth.md` - how to write `includes/html/graphs/*/auth.inc.php`: authorization, title/device setup, and the full variable contract.
- `23-Device-Overview-Panel.md` - compact application panels on the Device Overview page.

Keep display code light. Heavy processing should happen during polling and be stored in sensors, RRDs, `application_metrics`, or `$app->data`.
