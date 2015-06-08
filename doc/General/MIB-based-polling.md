The overall design of MIB-based support is:

Discovery:
  - MIBs are not involved; any work done here would have to be 
    duplicated by the poller and thus would only increase load.

Polling:
  - Look for a MIB matching sysObjectID in the MIB directory; if one
    is found:
    - parse it
    - walk that MIB on the device
    - store any numeric results in individual RRD files
    - update/add graph definitions in the database
  - Individual OSes (includes/polling/os/*.inc.php) can poll extra MIBs
    that should be there for a given OS by calling poll_mib().
  - Devices may be excluded from MIB polling by adding poll_mib = 0 to
    devices_attribs (see /device/device=ID/tab=edit/section=modules/)

Graphing:
  - For each file in the device directory, create a graph using the
    definition in the database.  Future enhancements:
    - Allow graphs to go in different sections
    - Allow graphs to be combined automatically or on a user-defined
      basis.

