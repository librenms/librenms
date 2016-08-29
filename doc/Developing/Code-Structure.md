source: Developing/Code-Structure.md
# Code structure.

This document will try and provide a good overview of how the code is structured within LibreNMS. We will go through the main directories and provide information on how and when they are used.

### doc/
This is the location of all the documentation for LibreNMS, this is in GitHub markdown format and can be viewed [online](http://docs.librenms.org/)

### LibreNMS/
Any classes should be under this directory, with a directory structure that matches the namespace.  One class per file. See [PSR-0](http://www.php-fig.org/psr/psr-0/) for details.

### html/
All web accessible files are located here.
### html/api_v0.php
This is the API routing file which directs users to the correct API function based on the API endpoint call.
### html/index.php
This is the main file which all links within LibreNMS are parsed through. It loads the majority of the relevant includes needed for the control panel to function. CSS and JS files are also loaded here.
### html/css
All used css files are located here. Apart from legacy files, anything in here is now a symlink.
### html/forms
This folder contains all of the files that are dynamically included from an ajax call to html/ajax_form.php.
### html/includes
This is where the majority of the website core files are located. These tend to be files that contain functions or often used code segments that can be included where needed rather than duplicating code.
### html/includes/api_functions.inc.php
All of the functions and calls for the API are located here.
### html/includes/authenticate.inc.php, html/includes/authentication/
These files are what provides the authentication layer to the user. In the folder are separate files based on the auth type used, this means new authentication types can be added easily enough.
### html/includes/functions.inc.php
This contains the majority of functions used throughout the standard web ui.
### html/includes/reports/
In here is a list of of files that generate PDF reports available to the user. These are dynamically called in from html/pdf.pdf based on the report the user requests.
### html/includes/table/
This folder contains all of the ajax calls when generating the table of data. Most have been converted over so if you are planning to add a new table of data then you will do so here for all of the back end data calls.
### html/js/
All used JS files are located here. Apart from legacy files, anything in here is now a symlink.
### html/pages
This folder contains the URL structure when browsing the Web UI. So for example /devices/ is actually a call to html/pages/devices.inc.php, /device/tab=ports/ is html/pages/device/ports.inc.php.

### includes/
This folder is quite big and contains all the files to make the cli and polling / discovery to work.
### includes/discovery/, includes/polling/
All the discovery and polling code. The format is usually quite similar between discovery and polling. Both are made up of modules and the files within the relevant directories will match that module. So for instance if you want to update the os detection for a device, you would look in includes/discovery/os/ for a file named after the operating system such as linux: includes/discovery/linux.inc.php. Within here you would update or add support for newer OS'. This is the same for polling as well.

### lib/
This is for all of the libraries used by LibreNMS. If you are including a 3rd party module, you would add the files in here either via git subtree if it's hosted on GitHub or just by copying the folder. Please ensure you maintain any copyright notices. You will then need to either reference the files in this folder directly from where you need them or alternatively as is the case with css and js libraries then symlink the needed files.

### logs/
Usually contains your web servers logs but can also contain poller logs and other items,

### mibs/
Here is where all of the mibs are located, traditionally this has meant having all mibs in one directory but for certain vendors this has changed and these are now located in sub folders.

### rrd/
Simple enough, this is where all of the rrd files are created. They are stored in folders based on the device hostname.

### sql-schema/
In here are all of the SQL schema files. These are used to setup a new instance of LibreNMS automatically when build-base.php is called or when an update is done and includes/sql-schema/update.php is called.
