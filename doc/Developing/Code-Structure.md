# Code structure

This document gives an overview of the code structure of LibreNMS. It
describes the main directories and their use. LibreNMS uses
[Laravel](https://laravel.com/docs/) for much of its web interface code
and database code. Much of the Laravel documentation therefore applies:
<https://laravel.com/docs/structure>

The filtered structure tree below holds the most important directories
for development:

```text
.
├─ app
├─ database
│  └─ migrations
├─ doc
├─ html
│  ├─ css
│  │  └─ custom
│  └─ js
├─ includes
│  ├─ definitions
│  ├─ discovery
│  ├─ html
│  │  ├─ forms
│  │  ├─ graphs
│  │  ├─ pages
│  │  └─ reports
│  └─ polling
├─ LibreNMS
├─ logs
├─ mibs
└─ rrd
```

### doc/

This directory holds all the LibreNMS documentation in GitHub markdown
format. The documentation is also [online](@= config.site_url =@).

### app/

This directory holds most Laravel classes and Eloquent classes.

### LibreNMS/

This directory holds the classes outside the Laravel application. The
directory structure matches the namespace. Put one class in each file.
For the details, read [PSR-0](http://www.php-fig.org/psr/psr-0/).

### html/

This directory holds all the legacy web files. A new page must obey the
Laravel conventions.

### html/api_v0.php

This file routes the API. It sends each API endpoint call to the
correct API function.

### html/index.php

All LibreNMS links go through this main file. It loads most of the
includes of the control panel. It also loads the CSS files and the JS
files.

### html/css/

All used CSS files are located here.

### html/css/custom/

Put your own CSS files in this directory. The automatic updates then
ignore them.

### html/js/

All used JS files are located here.

### includes/

This large directory holds the files of the command line, the polling,
and the discovery. The Laravel code cannot reach this code. This limit
is intentional.

### includes/discovery/, includes/polling/

These directories hold the discovery code and the polling code. The two
formats are similar. Both use modules. Each file in a directory has the
name of its module. For example, to update the OS detection of a
device, open `includes/discovery/os/`. Find the file with the name of
the operating system, such as `includes/discovery/linux.inc.php`. In
this file, update the OS support or add a new OS. The polling code
works in the same way.

### includes/html/

This directory holds most of the core website files. These files hold
functions and common code segments. Other files include them and
therefore do not duplicate the code.

### includes/html/forms/

This directory holds the files of the ajax calls to `ajax/form`.

### includes/html/api_functions.inc.php

This file holds all the API functions and calls.

### includes/html/functions.inc.php

This file holds most of the functions of the standard web interface.

### includes/html/graphs/

This directory holds the global graph definitions and the OS specific
graph definitions.

### includes/html/reports/

These files generate the PDF reports for the user. `html/pdf.php` calls
the correct file for the report of the request.

### includes/html/table/

This directory holds the ajax calls of the data tables. Most tables use
this method. Put the backend data calls of a new data table in this
directory.

### includes/html/pages/

This directory holds the URL structure of the web interface. For
example, `/devices/` calls `includes/html/pages/devices.inc.php`. The
URL `/device/tab=ports/` calls
`includes/html/pages/device/ports.inc.php`.

### logs/

This directory holds the main `librenms.log` file. It can also hold
your web server logs, the poller logs, and other items.

### mibs/

This directory holds all the MIBs. Put the standard MIBs in the root
directory. Put the MIBs of a vendor in their own subdirectory.

### rrd/

LibreNMS creates all the rrd files in this directory. Each device has
its own directory with the name of its hostname.

### database/migrations

This directory holds all the database migrations. For more
information, read the Laravel documentation:
<https://laravel.com/docs/migrations>

To create a new table, run:

```bash
php artisan make:model ModelName -m -c -r
```
