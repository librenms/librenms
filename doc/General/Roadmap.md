- Device support:
    - Ruckus wireless controllers
    - Investigate generic device support based on MIBs.  It should be
      possible to do basic graphs based just on the MIB.  They would
      obviously not be as customised as the specifically supported devices
      but should still be useful to users.

- Functionality/performance improvements:
    - Investigate solutions for poller performance improvement.
    - Investigate solutions for multiple communities per device.
    - Eliminate interface churn for transient interfaces (e.g. ppp/tun) on
      net-snmp.

- Consider adding some non-monitoring administrative functions:
    - enabling/disabling ports
    - changing access port VLANs
    - editing port labels

- Integrate as many usability improvements as time permits:
    - Front page: more automation; GUI configuration
