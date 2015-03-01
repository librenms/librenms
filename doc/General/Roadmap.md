- Device support:
    - Ruckus wireless controllers (Paul)
    - Investigate generic device support based on MIBs.  It should be
      possible to do basic graphs based just on the MIB.  They would
      obviously not be as customised as the specifically supported devices
      but should still be useful to users.

- Functionality/performance improvements:
    - Investigate solutions for poller performance improvement. (Tyler)
    - Investigate solutions for multiple communities per device. (tooms)
    - Eliminate interface churn for transient interfaces (e.g. ppp/tun) on
      net-snmp.

- ~~Integrate Nagios-based alerting.  Allow user to choose their preferred
  Nagios distribution/fork.~~ Alerting is now present.

- Consider adding some non-monitoring administrative functions:
    - enabling/disabling ports
    - changing access port VLANs
    - editing port labels

- Integrate as many usability improvements as time permits:
    - ~~Integrate nice menus like current Observium?~~ UI Design improved
    - Front page: more automation; GUI configuration?

- ~~Improve / Change alerting system~~ Alerting is now present.
