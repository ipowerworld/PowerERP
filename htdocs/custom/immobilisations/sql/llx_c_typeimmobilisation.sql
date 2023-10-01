# Host: localhost  (Version 5.7.36)
# Date: 2023-02-08 09:44:48
# Generator: MySQL-Front 6.0  (Build 2.20)


#
# Structure for table "llx_c_typeimmobilisation"
#

DROP TABLE IF EXISTS `llx_c_typeimmobilisation`;

CREATE TABLE `llx_c_typeimmobilisation` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(3) DEFAULT '1',
  PRIMARY KEY (`rowid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE llx_c_typeimmobilisation;

INSERT INTO `llx_c_typeimmobilisation` (`code`, `label`, `active`) VALUES ('COR', 'Corporelle', '1');
INSERT INTO `llx_c_typeimmobilisation` (`code`, `label`, `active`) VALUES ('INC', 'Incorporelle', '1');
INSERT INTO `llx_c_typeimmobilisation` (`code`, `label`, `active`) VALUES ('FIN', 'Financière', '1');

#
# Data for table "llx_c_typeimmobilisation"
#

