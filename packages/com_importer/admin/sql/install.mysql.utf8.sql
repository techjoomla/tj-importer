DROP TABLE IF EXISTS `#__importer_batches`;

CREATE TABLE `#__importer_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_name` varchar(250) NOT NULL,
  `client` varchar(150) NOT NULL,
  `import_status` varchar(250) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_date` datetime NOT NULL,
  `created_user` int(10) NOT NULL,
  `params` TEXT NOT NULL,
  `start_id` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=37 ;

CREATE TABLE IF NOT EXISTS `#__importer_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) NOT NULL,
  `validated` tinyint(1) NOT NULL,
  `imported` tinyint(1) NOT NULL,
  `invalid_columns` TEXT NOT NULL,
  `data` LONGTEXT NOT NULL,
  `content_id` varchar(250) NOT NULL,
  `import_error` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=87 ;
