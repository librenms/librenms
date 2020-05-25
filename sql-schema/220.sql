CREATE TABLE entityState (entity_state_id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT, device_id INT(11), entPhysical_id INT(11), entStateLastChanged DATETIME, entStateAdmin INT(11), entStateOper INT(11), entStateUsage INT(11), entStateAlarm TEXT, entStateStandby INT(11));
CREATE INDEX entityState_device_id_index ON entityState (device_id);
