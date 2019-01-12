ALTER TABLE `bgpPeers` ADD `vrf_id` int(11) AFTER `device_id`;
ALTER TABLE `vrfs` ADD `bgpLocalAs` int(10) UNSIGNED DEFAULT NULL AFTER `vrf_name`;
