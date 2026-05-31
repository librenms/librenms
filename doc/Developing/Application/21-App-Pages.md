---
title: 2.1 App Pages
description: How to design application pages under the Device Apps tab in LibreNMS.
tags:
  - developing
  - applications
---

# 2.1 App Pages

This chapter describes how to design application pages under the **Device > Apps** tab.

The purpose of an app page is to help the user answer questions quickly:

- Is the application healthy?
- What changed recently?
- Which instance needs attention?
- Where do I click for more detail?

Keep the page focused on presentation and navigation. Data collection, sensor creation, RRD writing, and database updates belong in the application handler and polling path, not in the UI file.

## Where app pages fit

App pages are device-local views for one application instance on one device.

```text
device/device={id}/tab=apps/app={app_type}
```

Use an app page when the application needs one or more of the following:

- graphs
- current status tables
- per-instance drill-down
- grouped sections
- links from sensors or overview panels
- a human-friendly summary of app-specific data

Do not use an app page to perform discovery, polling, normalization, expensive parsing, or long-running calculations.

## Start with the user task

Before writing the page, decide what the user is trying to do.

| User task | Good UI shape |
|---|---|
| Check overall health | Summary panel + key graphs |
| Compare a few fixed metrics | Flat graph list |
| Browse logical groups | Section tabs |
| Find a broken disk/container/repository | Status table + drill-down |
| Inspect one selected entity | Focused detail page |
| Switch graph type for one entity | Graph selector |
| Handle many entities | Table, search/filter, or compact navigation |

Avoid designing the page around internal data structures. The payload shape is not the UI shape.

## Common page patterns

### Pattern 1: Summary + key graphs

Use this for simple applications or as the default landing page for larger applications.

```text
+-----------------------------------------------------+
| App name                                            |
+-----------------------------------------------------+
| Status: OK   Instances: 4   Errors: 0               |
+-----------------------------------------------------+
| [ Key graph 1 ]                                     |
| [ Key graph 2 ]                                     |
+-----------------------------------------------------+
```

Use this when:

- the app has a small number of important metrics
- most users only need a quick health check
- per-instance details are secondary

Keep the first screen boring and useful. A wall of graphs is not a dashboard; it is punishment with axes.

### Pattern 2: Flat graph list

Use this when the app has a fixed, small set of graphs.

```text
+-----------------------------------------------------+
| App name                                            |
+-----------------------------------------------------+
| Requests                                            |
| [ graph ]                                           |
+-----------------------------------------------------+
| Latency                                             |
| [ graph ]                                           |
+-----------------------------------------------------+
| Errors                                              |
| [ graph ]                                           |
+-----------------------------------------------------+
```

Use this when:

- there are no meaningful instances
- the graph list is stable
- users normally want all graphs together

Avoid this pattern if the app has many instances. Repeating the same graph set for 30 disks, repositories, or containers becomes noise fast.

### Pattern 3: Section tabs

Use section tabs when the app has a few clear functional areas.

```text
+-----------------------------------------------------+
| App name                                            |
+-----------------------------------------------------+
| [System] | [Queries] | [Cache] | [Replication]      |
+-----------------------------------------------------+
| Selected section content                            |
| [ graphs / tables ]                                 |
+-----------------------------------------------------+
```

Good section names describe what the user is looking at, not where the data came from.

Good:

- `System`
- `Queries`
- `Cache`
- `Replication`
- `Errors`

Weak:

- `Data 1`
- `General`
- `Other`
- `JSON`
- `Metrics`

Tabs work best when there are few sections. If you need more than about five or six tabs, the app probably needs a table or a deeper drill-down structure instead.

### Pattern 4: Overview + per-instance detail

Use this when the app reports multiple entities such as disks, arrays, repositories, databases, containers, peers, or jobs.

```text
Overview page
+-----------------------------------------------------+
| App name                                            |
+-----------------------------------------------------+
| Summary: 12 instances, 1 warning, 0 critical        |
+-----------------------------------------------------+
| Name        Status      Key value       Last change |
| alpha       OK          42              5 min ago   |
| beta        Warning     3 errors        5 min ago   |
| gamma       OK          17              5 min ago   |
+-----------------------------------------------------+
| [ overview graphs ]                                 |
+-----------------------------------------------------+

Focused page
+-----------------------------------------------------+
| App name / beta                                     |
+-----------------------------------------------------+
| Status summary                                      |
| [ instance graphs ]                                 |
| [ recent errors / details ]                         |
+-----------------------------------------------------+
```

Use the overview page to help users choose what to inspect. Use the focused page to show details for the selected entity.

For low entity counts, compact links are fine:

```text
Overview | alpha | beta | gamma
```

For high entity counts, do not print a huge `Instances:` link list. Prefer a table, filter, or grouped view. A 100-item option bar is a crime scene.

### Pattern 5: Table + supporting graphs

Use a table when the latest values matter as much as historical graphs.

```text
+-----------------------------------------------------+
| App name                                            |
+-----------------------------------------------------+
| Name       Status     Value      Detail             |
| disk1      OK         34 C       [open]             |
| disk2      Warning    51 C       [open]             |
+-----------------------------------------------------+
| Temperature                                         |
| [ graph ]                                           |
+-----------------------------------------------------+
```

Tables are useful for:

- current state
- inventory fields
- per-entity status
- last run / last error
- capacity or usage summaries
- links to focused views

Keep table columns intentional. If every payload field becomes a column, the UI becomes a spreadsheet wearing a trench coat.

### Pattern 6: Focused graph selector

Use this when one selected entity has multiple graph types.

```text
+-----------------------------------------------------+
| App name / eth0                                     |
+-----------------------------------------------------+
| [Bits] | [Packets] | [Errors] | [Discards]          |
+-----------------------------------------------------+
| [ selected graph ]                                  |
+-----------------------------------------------------+
```

Use this pattern for deep detail pages, not as the main landing page unless the app is naturally graph-first.

## Choosing navigation

Use navigation that matches the amount of data.

| Amount of data | Preferred navigation |
|---|---|
| 1-5 fixed sections | Option bar / tabs |
| 1-10 instances | Compact instance links |
| 10-100 instances | Table with clickable rows |
| 100+ instances | Table with filtering, grouping, or search |
| Nested data | Overview page + focused detail pages |

URL parameters should describe the selected view clearly:

| Parameter | Use for |
|---|---|
| `section` | selected logical section |
| `instance` | selected instance/entity |
| `graph` | selected graph type |
| app-specific key, e.g. `repo`, `disk`, `array` | when the entity type is clearer than generic `instance` |

Prefer stable, readable parameters. Links from sensors, app overview panels, and alert messages may depend on them.

## Page hierarchy

A good app page usually has this order:

1. App title / context
2. Summary status
3. Navigation
4. Current important values
5. Graphs
6. Detailed tables or secondary information

Put the most operationally useful information first. Users should not have to scroll past decorative graphs to find the broken thing.

## Status presentation

Show status in a consistent and boring way.

Good status UI:

- uses the same severity language as LibreNMS where possible
- makes warnings and errors easy to scan
- includes enough context to understand the problem
- links to the relevant focused view

Poor status UI:

- uses custom color meanings
- hides problems inside long text blocks
- shows raw payload strings without explanation
- makes all rows look equally important

Recommended status order:

```text
Critical / Error
Warning
Unknown
OK
```

## Empty and missing data states

Do not render a broken-looking page when there is no data.

Handle these cases deliberately:

| Case | Recommended UI behavior |
|---|---|
| App enabled but never polled | Show a short “No data collected yet” message |
| App returned an error | Show app status and last error if available |
| No instances discovered | Show empty state, not an empty table |
| Selected instance no longer exists | Show “instance not found” and link back to overview |
| Graph has no RRD data yet | Let the graph area be empty, but keep the page usable |

Empty states should be short. Do not write a novel where a helpful sentence would do.

## Graph placement

Graphs are useful when they answer a trend question.

Good graph titles answer “what is this?” without requiring code knowledge:

- `Request rate`
- `Query latency`
- `Disk temperature`
- `Repository size`
- `Backup duration`

Weak graph titles expose implementation details:

- `metric1`
- `rrd_ds_0`
- `app_value`
- `data_count`

When possible, place summary graphs on the overview page and detailed graphs on focused pages.

## Tables

Use tables for current state and comparison, not for dumping raw payloads.

Good columns:

- Name
- Status
- Current value
- Last update
- Error count
- Usage
- Link/details

Avoid columns that are only meaningful to the developer unless the page is explicitly diagnostic.

For wide entities, split details into a focused page instead of making a 20-column table.

## Raw and diagnostic data

Raw payload values can be useful, but they should not dominate the normal UI.

Good places for raw/diagnostic data:

- collapsed “details” section
- debug-only output
- focused diagnostic tab
- documentation examples

Bad places:

- top of the landing page
- every table row by default
- graph titles
- status labels

## Minimal implementation contract

The UI file should be thin. It should read prepared data and render a view.

App pages live in:

```text
includes/html/pages/device/apps/{app_type}.inc.php
```

The page receives these variables:

| Variable | Description |
|---|---|
| `$device` | Device array |
| `$app` | Application model |
| `$vars` | URL parameters |
| `$graph_array` | Graph configuration passed to graph rendering includes |

Keep implementation details small and local:

```php
<?php

// Read prepared app data.
$data = $app->data ?? [];

// Select view from URL state.
$section = $vars['section'] ?? 'overview';
$instance = $vars['instance'] ?? null;

// Render navigation, tables, and graphs.
// Do not poll, discover, parse heavy payloads, or perform expensive work here.
```

## Safety and escaping

Treat names and values from payloads as untrusted display data.

Escape values before printing them into HTML unless the value is generated by a trusted LibreNMS helper that already handles escaping.

```php
htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
```

This especially applies to:

- instance names
- disk names
- repository names
- container names
- status strings from external tools
- error messages from agents

## App page checklist

Before opening a pull request, check the page against this list:

- The first screen answers whether the app is healthy.
- The page has a clear overview state.
- Large instance lists are shown as tables, not giant link bars.
- URL parameters are stable and readable.
- Payload-derived values are escaped.
- Heavy work is done during polling, not rendering.
- Empty states are handled deliberately.
- Graph titles are user-facing, not implementation-facing.
- The page links cleanly to focused views when relevant.
- The UI follows existing LibreNMS visual patterns instead of inventing a new mini-framework.
