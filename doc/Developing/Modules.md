> This document is based around `cisco-qfp` module as a example. 

# Intro
When starting your work on the new LibreNMS module you should ask yourself the following questions:

1. Which operating systems or devices this module relates to?
1. What should happen in the discovery phase?
1. What should happen in the polling phase?
1. What kind of storage do I need? (RRDs, database, etc.)
1. How should users interact with data gathered by this module (Web UI)


# Discovery and polling modules

## Define new modules

First we need to add the module to the list of available modules in `includes/defaults.inc.php` file. This file includes default configuration, including lists of discovery and polling modules.

```
$config['discovery_modules']['cisco-qfp']            = false;
$config['poller_modules']['cisco-qfp']               = false;
```

Modules in LibreNMS can be enabled by default for a specific operating system or can require the user to manually enable the module. Depending on how many devices running specific operating system your module is relevant to, you can choose to enable it by default or leave the choice to the user. For example, if you're writing a module for a specific device that shares the OS as many others that don't support the MIB you're polling, there's no need for that module to be enabled by default for that OS because the percentage of devices that will actually be able to collect data using the module is low. You should also consider leaving the module disabled if the polling takes a long time to complete. Many users choose to run LibreNMS with 60 second intervals, and enabling modules that take longer than that will cause problems for those users.

If your module should be enabled for a specific OS, you can edit OS definition YAML file in `includes/definitions/` directory and add it to `poller_modules` and/or `discovery_modules`. For example, if we want to enable `cisco-qfp` module for IOS-XE we edit `includes/definitions/iosxe.yaml`:

```
poller_modules:
    cisco-qfp: true
discovery_modules:
    cisco-qfp: true
```

## Write the code for Discovery and polling

Files with discovery and polling code are located in `includes/discovery/` and  `includes/polling/` directories respectively.

Discovery phase usually collects data about the devices that will be helpfull in polling phase. This data is usually something that is not changed regually and can be updated in longer intervals. For example, device type, hardware, operating system or serial numbers are kind of things that are not expected to change regually between polling cycles. This helps save time during polling phase and also allows even to generate OIDs that should be polled in advance for the polling module.

In contrast, polling phase focuses on the statistics that change regularly. Data like interface statistics, CPU utilization, used RAM and disk space are expected to change continiously. This is the data that we usually graph and monitor through time or create alerts for. Polling is based on the information that is gathered by discovery module and you should avoid walking the whole MIB tables (if not nececary) or doing discovery of new components. Polling should be as fast as possible because even though one module alone is not exceeding the polling interval time, we should consider combined time for multiple modules, and those that will be written in the future.

Most of the data in LibreNMS is stored in RRD files. This is a great storage when you need to store numeric data that should be graphed, but in many cases you will need some additional data saved such as list of components that you collect the data for, text descriptions, statuses or something alert related. You should consider using Components if possible instead of creating new database tables, especially if the module data is not complex or is highly specific to some device or vendor.


# Web UI

Depending on what your module does, you should choose the best place to display module's data in the Web UI. Some of the modules logically build of top of other modules and you can extend existing pages but some are standalone and require completly new sections in the UI.

## Write code for HTML pages and graphs

Below is the list of directories that could be interesting while developing a new module:

 - `includes/html/pages/` - Code rendering HTML pages
 - `includes/html/pages/device/` - Code rendering HTML pages for a device
 - `includes/graphs/` - Generic code for generating graphs from RRD files
 - `includes/graphs/device/` - Code for generating graphs for device
 - `includes/graphs/port/` - Code for generating graphs for perts

When you choose the location in the UI to display your data, you should ideally group it in a section with similar data. Avoid making new tabs (on the same level as Overview, Graphs, Health) if not nececary. Since the place on users display is limited, we should pay atention to the organisation of data inside tabs and sections.

When writing code for generating graphs, see if you can reuse existing general purpose graph definitions inside `includes/graphs/` directory.

