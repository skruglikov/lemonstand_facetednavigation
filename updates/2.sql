CREATE TABLE `facetednavigation_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `url_name` varchar(100) DEFAULT NULL,
  `css` varchar(100) DEFAULT NULL,
  `enbled` int(11) DEFAULT NULL,
  `is_disabled` tinyint(4) DEFAULT NULL,
  `front_end_sort_order` int(11) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `api_code` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;