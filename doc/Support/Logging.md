source: Support/Logging.md

### General

LibreNMS provides various logging information for it's normal operations.
The default log level is info and will provide standard information such as 
poller and discovery runs.

You can alter the debug level within the WebUI under the Logging Settings section.

![Logging Settings](/img/logging-level-webui.png)

The following levels are available:

  - none: No logging information will be recorded.
  - info: Standard poller and discovery run information
  - error: info logging + MySQL errors and Billing errors
  - debug: info + error + syslog debug information.
  
> It is only recommended to enable debug whilst you are troubleshooting syslog support.