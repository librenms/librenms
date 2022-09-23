ALTER TABLE port_in_measurements ADD INDEX (`port_id`, `timestamp`);
ALTER TABLE port_out_measurements ADD INDEX (`port_id`, `timestamp`);
ALTER TABLE bill_data ADD INDEX (`bill_id`, `timestamp`);
