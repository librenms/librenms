source: Extensions/PeeringDB.md
path: blob/master/doc/

# PeeringDB Support

LibreNMS has integration with PeeringDB to match up your BGP sessions
with the peering exchanges you are connected to.

To enable the integration please do so within the WebUI -> Global
Settings -> External Settings -> PeeringDB Integration.

Data will be collated the next time daily.sh is run or you can
manually force this by running `php daily.php -f peeringdb`, the
initial collection is delayed for a random amount of time to avoid
overloading the PeeringDB API.

Once enabled you will have an additional menu item under Routing -> PeeringDB
