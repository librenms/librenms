<?php

if ($_POST['action'] == 'delete_bill' && $_POST['confirm'] == 'confirm') {
    dbDelete('bill_history', '`bill_id` = ?', [$bill_id]);
    dbDelete('bill_ports', '`bill_id` = ?', [$bill_id]);
    dbDelete('bill_data', '`bill_id` = ?', [$bill_id]);
    dbDelete('bill_perms', '`bill_id` = ?', [$bill_id]);
    dbDelete('bills', '`bill_id` = ?', [$bill_id]);

    echo '<div class=infobox>Bill Deleted. Redirecting to Bills list.</div>';

    echo "<meta http-equiv='Refresh' content=\"2; url='bills/'\">";
}

if ($_POST['action'] == 'reset_bill' && ($_POST['confirm'] == 'rrd' || $_POST['confirm'] == 'mysql')) {
    if ($_POST['confirm'] == 'mysql') {
        dbDelete('bill_history', '`bill_id` = ?', [$bill_id]);
        dbDelete('bill_data', '`bill_id` = ?', [$bill_id]);
    }

    if ($_POST['confirm'] == 'rrd') {
        // Stil todo
    }

    echo '<div class=infobox>Bill Resetting. Redirecting to Bills list.</div>';

    echo "<meta http-equiv='Refresh' content=\"2; url='bills/'\">";
}

if ($_POST['action'] == 'add_bill_port') {
    dbInsert(['bill_id' => $_POST['bill_id'], 'port_id' => $_POST['port_id']], 'bill_ports');
}

if ($_POST['action'] == 'delete_bill_port') {
    dbDelete('bill_ports', '`bill_id` =  ? AND `port_id` = ?', [$bill_id, $_POST['port_id']]);
}

if ($_POST['action'] == 'update_bill') {
    if (isset($_POST['bill_quota']) or isset($_POST['bill_cdr'])) {
        if ($_POST['bill_type'] == 'quota') {
            if (isset($_POST['bill_quota_type'])) {
                if ($_POST['bill_quota_type'] == 'MB') {
                    $multiplier = (1 * \LibreNMS\Config::get('billing.base'));
                }

                if ($_POST['bill_quota_type'] == 'GB') {
                    $multiplier = (1 * \LibreNMS\Config::get('billing.base') * \LibreNMS\Config::get('billing.base'));
                }

                if ($_POST['bill_quota_type'] == 'TB') {
                    $multiplier = (1 * \LibreNMS\Config::get('billing.base') * \LibreNMS\Config::get('billing.base') * \LibreNMS\Config::get('billing.base'));
                }

                $bill_quota = (is_numeric($_POST['bill_quota']) ? $_POST['bill_quota'] * \LibreNMS\Config::get('billing.base') * $multiplier : 0);
                $bill_cdr = 0;
            }
        }

        if ($_POST['bill_type'] == 'cdr') {
            if (isset($_POST['bill_cdr_type'])) {
                if ($_POST['bill_cdr_type'] == 'Kbps') {
                    $multiplier = (1 * \LibreNMS\Config::get('billing.base'));
                }

                if ($_POST['bill_cdr_type'] == 'Mbps') {
                    $multiplier = (1 * \LibreNMS\Config::get('billing.base') * \LibreNMS\Config::get('billing.base'));
                }

                if ($_POST['bill_cdr_type'] == 'Gbps') {
                    $multiplier = (1 * \LibreNMS\Config::get('billing.base') * \LibreNMS\Config::get('billing.base') * \LibreNMS\Config::get('billing.base'));
                }

                $bill_cdr = (is_numeric($_POST['bill_cdr']) ? $_POST['bill_cdr'] * $multiplier : 0);
                $bill_quota = 0;
            }
        }
    }//end if

    // NOTE: casting to string for mysqli bug (fixed by mysqlnd)
    if (dbUpdate(
        [
            'bill_name'   => $_POST['bill_name'],
            'bill_day'    => $_POST['bill_day'],
            'bill_quota'  => (string) $bill_quota,
            'bill_cdr'    => (string) $bill_cdr,
            'bill_type'   => $_POST['bill_type'],
            'dir_95th'    => $_POST['dir_95th'],
            'bill_custid' => $_POST['bill_custid'],
            'bill_ref'    => $_POST['bill_ref'],
            'bill_notes'  => $_POST['bill_notes'],
        ],
        'bills',
        '`bill_id` = ?',
        [$bill_id]
    )) {
        print_message('Bill Properties Updated');
    }
}//end if
