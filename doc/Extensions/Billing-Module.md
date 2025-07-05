# Billing Module

With the billing module you can create a bill, assign a quota to it
and add ports to it. It then tracks the ports usage and shows you the
usage in the bill, including any overage.
Accounting by both total transferred data and 95th percentile is supported.

To enable and use the billing module you need to perform the following steps:

!!! setting "system/billing"
    ```bash
    lnms config:set enable_billing true
    ```

=== "Cron"
    Edit `/etc/cron.d/librenms` and add the following:
    ```bash
    */5 * * * * librenms /opt/librenms/poll-billing.php >> /dev/null 2>&1
    01  * * * * librenms /opt/librenms/billing-calculate.php >> /dev/null 2>&1
    ```

=== "Dispatcher Service"
    Go to Settings -> Poller -> Settings
    And for each poller, ensure `Billing Enabled` is selected.

## Adding a bill

To create a new bill, from the LibreNMS menu select Ports -> Traffic Bills and
select `+ Create Bill`.

Enter the relevant details within the form, ensuring that you select at least
one device and port.

## 95th Percentile Calculation

For 95th Percentile billing, the default behavior is to use the
highest of the input or output 95th Percentile calculation.

To instead use the combined total of inout + output to derive the 95th percentile,
This can be changed on a per bill basis by setting 95th Calculation to "Aggregate".

!!! setting "system/billing"
    ```bash
    lnms config:set billing.95th_default_agg true
    ```

This configuration setting is cosmetic and only changes the default
selected option when adding a new bill.