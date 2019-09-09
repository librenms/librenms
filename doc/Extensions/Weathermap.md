source: Extensions/Weathermap.md
path: blob/master/doc/

# Network-WeatherMap with LibreNMS

Integrating LibreNMS with Network-Weathermap, allows you to build network
maps to help visulaize network traffic flow rates.
[Link](https://network-weathermap.com/) to Network-Wearthermap Offical
Website

## Prerequisites

Network-WeatherMap requires php pear to work.

## Installing Network-WeatherMap

## Step 1

Extract to your LibreNMS plugins directory `/opt/librenms/html/plugins`
so you should see something like `/opt/librenms/html/plugins/Weathermap/`
The best way to do this is via git. Go to your install directory and
then `/opt/librenms/html/plugins`
Enter:
    `git clone https://github.com/librenms-plugins/Weathermap.git`

## Step 2

Inside the html/plugins directory, change the ownership of the
Weathermap directory by typing `chown -R librenms:librenms Weathermap/`
Make the configs directory writeable `chmod 775 /opt/librenms/html/plugins/Weathermap/configs`
Note if you are using SELinux you need to input the following
command `chcon -R -t httpd_cache_t Weathermap/`

## Step 3

Enable the cron process by editing your current LibreNMS cron file
(typically /etc/cron.d/librenms) and add the following:

```
*/5 * * * * librenms /opt/librenms/html/plugins/Weathermap/map-poller.php >> /dev/null 2>&1
```

## Step 4

Enable the plugin from LibreNMS Web UI in OverView ->Plugins -> Plugin Admin menu.

## Step 5

Now you should see Weathermap Overview -> Plugins -> Weathermap
Create your maps, please note when you create a MAP, please click Map
Style, ensure Overlib is selected for HTML Style and click submit.
Also, ensure you set an output image filename and output HTML filename in Map Properties.
I'd recommend you use the output folder as this is excluded from git
updates (i.e enter output/mymap.png and output/mymap.html).

Optional: If your install is in another directory than standard, set
`$basehref` within `map-poller.php`.

# WeatherMapper

Automatically generate weathermaps from a LibreNMS database using WeatherMapper [Link](https://github.com/pblasquez/weathermapper)

![Example Network Weather Map](/img/network-weather-map.PNG)

# Adding your Network Weathermaps to the Dashboards

Once you have created your Network Weather Map you can add it to a
dashboard page by doing the following.

## Step 1

When you create the Weathermap make sure to export as HTML and PNG you
will need this for the out to the dashboard.

In the Weathermap Plugin page, you will see the output maps. `Right
click` on one of the maps and click on `copy image address`.

Example URL: `http://yourlibrenms.org/plugins/Weathermap/output/yourmap.html`

## Step 2

Then go back to your Dashboard, create a new dashboard and give it a
name. select the widget as *External Images*.

Give the Widget a Title.

The *Image URL* will need to be the address you copied but at the end
remove the `.html` and replace it with `.png`

Example  *Image URL* `http://yourlibrenms.org/plugins/Weathermap/output/yourmap.png`

The *Target URL* will be the URL you copied but with the `.html` at
the end of the URL.

Example *Target URL* `http://yourlibrenms.org/plugins/Weathermap/output/yourmap.html`

Then Click on Set

You should now be able to see the Weathermap you have created in your
list of dashboards. You could also add this to existing dashboards.

![Example Network Weathermap Dashboard](/img/network-weathermap-dashboard.png)
