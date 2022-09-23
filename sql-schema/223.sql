ALTER TABLE `sensors` MODIFY `sensor_divisor` BIGINT(20) NOT NULL DEFAULT '1', CHANGE sensor_index sensor_index VARCHAR( 128 );
