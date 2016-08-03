DROP TABLE IF EXISTS `ipmi_sensors`;
CREATE TABLE `ipmi_sensors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hw_id` varchar(200) NOT NULL,
  `sensor_ipmi` varchar(100) NOT NULL,
  `sensor_display` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
LOCK TABLES `ipmi_sensors` WRITE;
INSERT INTO `ipmi_sensors` VALUES (1,'HP ProLiant BL460c G6','Temp 1','Ambient zone'),(2,'HP ProLiant BL460c G6','Temp 2','CPU1'),(3,'HP ProLiant BL460c G6','Temp 3','CPU2'),(4,'HP ProLiant BL460c G6','Temp 4','Memory zone'),(5,'HP ProLiant BL460c G6','Temp 5','Memory zone'),(6,'HP ProLiant BL460c G6','Temp 6','Memory zone'),(7,'HP ProLiant BL460c G6','Temp 7','System zone'),(8,'HP ProLiant BL460c G6','Temp 8','System zone'),(9,'HP ProLiant BL460c G6','Temp 9','System zone'),(10,'HP ProLiant BL460c G6','Temp 10','Storage zone'),(11,'HP ProLiant BL460c G1','Temp 1','System zone'),(12,'HP ProLiant BL460c G1','Temp 2','CPU1 zone'),(13,'HP ProLiant BL460c G1','Temp 3','CPU1'),(14,'HP ProLiant BL460c G1','Temp 4','CPU1'),(15,'HP ProLiant BL460c G1','Temp 5','CPU2 zone'),(16,'HP ProLiant BL460c G1','Temp 6','CPU2'),(17,'HP ProLiant BL460c G1','Temp 7','CPU2'),(18,'HP ProLiant BL460c G1','Temp 8','Memory zone'),(19,'HP ProLiant BL460c G1','Temp 9','Ambient zone');
UNLOCK TABLES;
