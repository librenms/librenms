<<<<<<< HEAD
ALTER TABLE `proxmox` ADD COLUMN `vmstatus` VARCHAR(50) NULL DEFAULT NULL AFTER `last_seen`, ADD COLUMN `vmtype` VARCHAR(4) NULL DEFAULT NULL AFTER `vmstatus`, ADD COLUMN `vmcpus` INT(11) NULL DEFAULT NULL AFTER `vmtype`, ADD COLUMN `vmuptime` INT(11) NULL DEFAULT NULL AFTER `vmcpus`, ADD COLUMN `vmpid` INT(11) NULL DEFAULT NULL AFTER `vmuptime`, ADD COLUMN `vmmem` INT(11) NULL DEFAULT NULL AFTER `vmpid`, ADD COLUMN `vmmaxmem` INT(11) NULL DEFAULT NULL AFTER `vmmem`, ADD COLUMN `vmmemuse` FLOAT NULL DEFAULT NULL AFTER `vmmaxmem`, ADD COLUMN `vmdisk` INT(11) NULL DEFAULT NULL AFTER `vmmemuse`, ADD COLUMN `vmmaxdisk` INT(11) NULL DEFAULT NULL AFTER `vmdisk`, ADD COLUMN `vmdiskuse` FLOAT NULL DEFAULT NULL AFTER `vmmaxdisk`;
=======
INSERT INTO `graph_types`(`graph_type`, `graph_subtype`, `graph_section`, `graph_descr`, `graph_order`) VALUES ('device',  'asyncos_conns',  'proxy',  'Current Connections',  0);
>>>>>>> de314b2835cb17a6c18a44ba4e9aa95f6302bbec
