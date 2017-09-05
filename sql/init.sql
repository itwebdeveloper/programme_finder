CREATE DATABASE programme_finder;
USE programme_finder;

CREATE TABLE `results` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `short_synopsis` TEXT NOT NULL,
  `image_pid` varchar(50) NOT NULL,
  `created_on` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`created_on`),
  FULLTEXT (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;