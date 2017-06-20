CREATE TABLE `facetednavigation_attribute_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `url_name` varchar(50) DEFAULT NULL,
  `description` text,
  `facet_id` int(11) DEFAULT NULL,
  `option_key` varchar(35) DEFAULT NULL,
  `front_end_sort_order` int(11) DEFAULT NULL,
  `option_in_set` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `facet_id` (`facet_id`),
  KEY `option_key` (`option_key`),
  KEY `option_in_set` (`option_in_set`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;