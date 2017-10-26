source: Extensions/Weathermap.md

# Network-WeatherMap with LibreNMS
Intergarting LibreNMS with Network-Weathermap, allows you to build network maps to visulaize network traffic flow rates.
[Link](https://network-weathermap.com/) to Network-Wearthermap Offical Website

### Installing Network-WeatherMap

### Step 1. 
Extract to your LibreNMS plugins directory `html/plugins` so you should see something like `/opt/librenms/html/plugins/Weathermap/`
The best way to do this is via git. Go to your install directory and then `html/plugins`.
Enter:
    `git clone https://github.com/librenms-plugins/Weathermap.git`
### Step 2.
Inside the librenms/html/plugins directory, change the ownership of the Weathermap directory by typing `chown -R librenms:librenms Weathermap/`
Make the configs directory writeable by your web server, either `chown apache:apache configs/` or `chmod 777 configs`.
I'd highly advise you choose the first option, replace `apache:apache` with your web servers user and group this will depend on what OS you are using and Web Server.
### Step 3. 
Enable the cron process by editing your current LibreNMS cron file (typically /etc/cron.d/librenms) and add the following:
LibreNMS:  `*/5 * * * * librenms /opt/librenms/html/plugins/Weathermap/map-poller.php >> /dev/null 2>&1`
### Step 4. 
Enable the plugin from LibreNMS Web UI in OverView ->Plugins -> Plugin Admin menu.

### Step 5. 
Now you should see Weathermap Overview -> Plugins -> Weathermap
Create your maps, please note when you create a MAP, please click Map Style, ensure Overlib is selected for HTML Style and click submit.
Also, ensure you set an output image filename and output HTML filename in Map Properties.
I'd recommend you use the output folder as this is excluded from git updates (i.e enter output/mymap.png and output/mymap.html).

Optional: If your install is in another directory than standard, set `$basehref` within `map-poller.php`.

Automatically generate weathermaps from a LibreNMS database [Link](https://github.com/pblasquez/weathermapper)

![Example Network Weather Map](/img/network-weather-map.PNG)
