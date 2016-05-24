UPDATE `config` SET `config_value` = '((%ports.ifInOctets_rate*8) / %ports.ifSpeed)*100' WHERE `config_name` = 'alert.macros.rule.port_usage_perc';
