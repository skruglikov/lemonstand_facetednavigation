CREATE TABLE `facetednavigation_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `facet_id` int(11) DEFAULT NULL,
  `value_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product` (`product_id`),
  KEY `facet` (`facet_id`),
  KEY `value` (`value_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;