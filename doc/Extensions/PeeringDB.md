# PeeringDB Support

LibreNMS connects to PeeringDB. It matches your BGP sessions to your
peering exchanges.

Enable the integration in the web interface.

!!! setting "external/peeringdb"
    ```bash
    lnms config:set peeringdb.enabled true
    ```

The next run of `daily.sh` collects the data. To force the collection,
run `php daily.php -f peeringdb`.

To keep the load on the PeeringDB API low, LibreNMS:

- waits a random 3 to 30 seconds before each request, so that installs
  do not query the API in lockstep
- keeps the collected data for 71 hours before it refreshes
- remembers for a week which of your ASNs PeeringDB holds no data for
  and skips them

A new menu item then appears under Routing -> PeeringDB.
