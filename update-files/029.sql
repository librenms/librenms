CREATE TABLE IF NOT EXISTS `session` (  `session_id` int(11) NOT NULL AUTO_INCREMENT,  `session_username` varchar(30) NOT NULL,  `session_value` varchar(60) NOT NULL,  `session_token` varchar(60) NOT NULL,  `session_auth` varchar(16) NOT NULL,  `session_expiry` int(11) NOT NULL,  PRIMARY KEY (`session_id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

