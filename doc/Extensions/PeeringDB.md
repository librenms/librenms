# PeeringDB Support

LibreNMS connects to PeeringDB. It matches your BGP sessions to your
peering exchanges.

Enable the integration in the web interface.

!!! setting "external/peeringdb"
    ```bash
    lnms config:set peeringdb.enabled true
    ```

The next run of `daily.sh` collects the data. To force the collection,
run `php daily.php -f peeringdb`. The first collection has a random
delay. This delay prevents an overload of the PeeringDB API.

A new menu item then appears under Routing -> PeeringDB.
