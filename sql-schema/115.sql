UPDATE `config` SET `config_value` = '((%ports.ifInOctets_rate*8) / %ports.ifSpeed)*100' WHERE `config_value` = '((%ports.ifInOctets_rate*8)/%ports.ifSpeed)*100';
UPDATE `config` SET `config_default` = '((%ports.ifInOctets_rate*8) / %ports.ifSpeed)*100' WHERE `config_default` = '((%ports.ifInOctets_rate*8)/%ports.ifSpeed)*100';
