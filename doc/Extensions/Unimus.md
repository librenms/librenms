# Unimus

Integrating LibreNMS with [Unimus](https://unimus.net) brings config
viewing directly into LibreNMS: the latest backup, the full backup
history, and diffs between any two backups are all available under
the Config tab of each device.

This integration needs a working Unimus deployment with config backups
of your devices. LibreNMS reads the backups through the Unimus API. It
does not add or manage devices in Unimus.

## Requirements

- Unimus with API v2 (Unimus 2.x or newer)
- An API token created in Unimus (`Settings -> User Management -> API tokens`).

## Configuration

Go to Unimus settings in the External Settings section of Global
Settings (`Settings -> Global Settings -> External -> Unimus
Integration`). Enable the integration, enter the URL of your Unimus
instance and enter your API token.

Alternatively, configure it from the CLI:

!!! setting "external/unimus"
    ```bash
    lnms config:set unimus.enabled true
    lnms config:set unimus.url http://127.0.0.1:8085
    lnms config:set unimus.token YOUR_API_TOKEN
    ```

A user with the show config permission then sees a Config tab on each
device in Unimus.

## Device matching

LibreNMS matches a device to its Unimus device by address. It tries
these values in order until one matches:

1. The LibreNMS hostname
1. The hostname with the domain stripped
1. The hostname with `mydomain` appended (if configured)
1. The device IP address

A successful match stays in the cache for one hour. A new device in
Unimus therefore takes some minutes to appear.

## Notes

- When Unimus support is enabled, the Unimus-backed Config tab
  takes over from the Oxidized/RANCID Config tab.
- The list holds the binary backups, but LibreNMS cannot show their
  content. Read them in Unimus.
- Unimus generates the diffs through its API. LibreNMS shows them as a
  unified diff and highlights the insertions, the deletions, and the
  changes.
