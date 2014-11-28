Roadmap
-------

- Device support:
  - Investigate generic device support based on MIBs.  It should be
    possible to do basic graphs based just on the MIB.  They would
    obviously not be as customised as the specifically supported devices
    but should still be useful to users.
  - Ruckus wireless controllers

- Functionality/performance improvements:
  - Eliminate interface churn for transient interfaces (e.g. ppp/tun)
    on net-snmp.
  - Investigate solutions for poller performance improvement.
  - Investigate solutions for multiple communities/ports per device.

- Integrate Nagios-based alerting.  Allow user to choose their preferred
  Nagios distribution/fork.

- Consider adding some non-monitoring administrative functions:
  - enabling/disabling ports
  - changing access port VLANs
  - editing port labels

- Integrate as many usability improvements as time permits:
  - Front page customisation
  - GUI configuration of most options
