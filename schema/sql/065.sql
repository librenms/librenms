DELETE n1 FROM pollers n1, pollers n2 WHERE n1.last_polled < n2.last_polled and n1.poller_name = n2.poller_name;
ALTER TABLE pollers ADD PRIMARY KEY (poller_name);
ALTER TABLE `devices` ADD `last_poll_attempted` timestamp NULL DEFAULT NULL AFTER `last_polled`;
ALTER TABLE `devices` ADD INDEX `last_polled` (`last_polled`);
ALTER TABLE `devices` ADD INDEX `last_poll_attempted` (`last_poll_attempted`);
