<?php

include("includes/defaults.inc.php");
include("config.php");

echo("Updating Billing...");

$create_sql = "CREATE TABLE IF NOT EXISTS `bill_history` (
  `bill_hist_id` int(11) NOT NULL AUTO_INCREMENT,
  `bill_id` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bill_datefrom` datetime NOT NULL,
  `bill_dateto` datetime NOT NULL,
  `bill_type` text NOT NULL,
  `bill_allowed` bigint(20) NOT NULL,
  `bill_used` bigint(20) NOT NULL,
  `bill_overuse` bigint(20) NOT NULL,
  `bill_percent` decimal(10,2) NOT NULL,
  `rate_95th_in` bigint(20) NOT NULL,
  `rate_95th_out` bigint(20) NOT NULL,
  `rate_95th` bigint(20) NOT NULL,
  `dir_95th` varchar(3) NOT NULL,
  `rate_average` bigint(20) NOT NULL,
  `rate_average_in` bigint(20) NOT NULL,
  `rate_average_out` bigint(20) NOT NULL,
  `traf_in` bigint(20) NOT NULL,
  `traf_out` bigint(20) NOT NULL,
  `traf_total` bigint(20) NOT NULL,
  `pdf` longblob,
  PRIMARY KEY (`bill_hist_id`),
  UNIQUE KEY `unique_index` (`bill_id`,`bill_datefrom`,`bill_dateto`),
  KEY `bill_id` (`bill_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;";

mysql_query("DROP TABLE `bill_history`");
mysql_query($create_sql);
mysql_query("ALTER TABLE `bills` ADD `bill_quota` bigint(20) NOT NULL AFTER  `bill_gb`;");

mysql_query("ALTER TABLE `bills` DROP `rate_95th_in`;");
mysql_query("ALTER TABLE `bills` DROP `rate_95th_out`;");
mysql_query("ALTER TABLE `bills` DROP `rate_95th`;");
mysql_query("ALTER TABLE `bills` DROP `dir_95th`;");
mysql_query("ALTER TABLE `bills` DROP `total_data`;");
mysql_query("ALTER TABLE `bills` DROP `total_data_in`;");
mysql_query("ALTER TABLE `bills` DROP `total_data_out`;");
mysql_query("ALTER TABLE `bills` DROP `rate_average_in`;");
mysql_query("ALTER TABLE `bills` DROP `rate_average_out`;");
mysql_query("ALTER TABLE `bills` DROP `rate_average`;");
mysql_query("ALTER TABLE `bills` DROP `bill_last_calc`;");

mysql_query("ALTER TABLE `bills` ADD `rate_95th_in` bigint(20) NOT NULL;");
mysql_query("ALTER TABLE `bills` ADD `rate_95th_out` bigint(20) NOT NULL;");
mysql_query("ALTER TABLE `bills` ADD `rate_95th` bigint(20) NOT NULL;");
mysql_query("ALTER TABLE `bills` ADD `dir_95th` varchar(3) NOT NULL;");
mysql_query("ALTER TABLE `bills` ADD `total_data` bigint(20) NOT NULL;");
mysql_query("ALTER TABLE `bills` ADD `total_data_in` bigint(20) NOT NULL;");
mysql_query("ALTER TABLE `bills` ADD `total_data_out` bigint(20) NOT NULL;");
mysql_query("ALTER TABLE `bills` ADD `rate_average_in` bigint(20) NOT NULL;");
mysql_query("ALTER TABLE `bills` ADD `rate_average_out` bigint(20) NOT NULL;");
mysql_query("ALTER TABLE `bills` ADD `rate_average` bigint(20) NOT NULL;");
mysql_query("ALTER TABLE `bills` CHANGE `bill_cdr` bigint(20) NOT NULL;");

foreach (dbFetchRows("SELECT * FROM `bills`") as $bill_data)
{
  echo("Bill ".$bill['bill_id']." ".$bill['bill_name']);
  if($bill_data['bill_gb'] > 0)
  {
    $bill_data['bill_quota'] = $bill_data['bill_gb'] * $config['billing']['base'] * $config['billing']['base'];
    dbUpdate(array('bill_quota' => $bill_data['bill_quota']), 'bills', '`bill_id` = ?', array($bill_data['bill_id']));
    echo("Quota -> ".$bill_data['bill_quota']);
  }

  if($bill_data['bill_cdr'] > 0)
  {
    $bill_data['bill_cdr'] = $bill_data['bill_cdr'] * 1000;
    dbUpdate(array('bill_cdr' => $bill_data['bill_cdr']), 'bills', '`bill_id` = ?', array($bill_data['bill_id']));
    echo("CDR -> ".$bill_data['bill_cdr']);
  }
  echo("\n");
}

mysql_query("ALTER TABLE `bills` DROP `bill_gb`");

?>
