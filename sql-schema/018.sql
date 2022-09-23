ALTER TABLE  `pseudowires` ADD  `device_id` INT NOT NULL AFTER  `pseudowire_id`;
TRUNCATE TABLE  `pseudowires`;
ALTER TABLE  `pseudowires` ADD  `pw_type` VARCHAR( 32 ) NOT NULL ,ADD  `pw_psntype` VARCHAR( 32 ) NOT NULL ,ADD  `pw_local_mtu` INT NOT NULL ,ADD  `pw_peer_mtu` INT NOT NULL ,ADD  `pw_descr` VARCHAR( 128 ) NOT NULL;
