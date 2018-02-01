UPDATE `ipv4_mac` SET `context_name`='' WHERE `context_name` IS NULL;
ALTER TABLE `ipv4_mac` MODIFY `context_name` VARCHAR(128) NOT NULL;
