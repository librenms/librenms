DELETE `l1` FROM `locations` `l1`, `locations` `l2` WHERE `l1`.`lat` = `l2`.`lat` AND `l1`.`lng` = `l2`.`lng` AND `l1`.`id` > `l2`.`id`;
ALTER TABLE `locations` ADD UNIQUE( `lat`, `lng`);
