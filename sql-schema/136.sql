UPDATE `ports` SET `ifLastChange` = 0;
ALTER TABLE `ports` CHANGE `ifLastChange` `ifLastChange` INT NOT NULL DEFAULT 0;
