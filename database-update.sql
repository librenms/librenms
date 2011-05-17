ALTER TABLE users ADD can_modify_passwd TINYINT NOT NULL DEFAULT 1;
ALTER TABLE  `observer_dev`.`storage` ADD UNIQUE  `index_unique` (  `device_id` ,  `storage_mib` ,  `storage_index` );
ALTER TABLE  `bgpPeers_cbgp` ADD  `AcceptedPrefixes` INT NOT NULL ,ADD  `DeniedPrefixes` INT NOT NULL ,ADD  `PrefixAdminLimit` INT NOT NULL ,ADD  `PrefixThreshold` INT NOT NULL ,ADD  `PrefixClearThreshold` INT NOT NULL ,ADD  `AdvertisedPrefixes` INT NOT NULL ,ADD  `SuppressedPrefixes` INT NOT NULL ,ADD  `WithdrawnPrefixes` INT NOT NULL;
ALTER TABLE  `observer_dev`.`bgpPeers_cbgp` ADD UNIQUE  `unique_index` (  `device_id` ,  `bgpPeerIdentifier` ,  `afi` ,  `safi` );
