# Host: localhost  (Version 5.7.36)
# Date: 2023-02-08 09:44:48
# Generator: MySQL-Front 6.0  (Build 2.20)


#
# Structure for table "llx_c_typeimmobilisation"
#

DROP TABLE IF EXISTS `llx_immobilisations_setup_fields`;

CREATE TABLE `llx_immobilisations_setup_fields` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `label` text COLLATE utf8_unicode_ci NULL,
  `active` tinyint(3) DEFAULT '1',
  PRIMARY KEY (`rowid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



#
# Data for table "llx_c_typeimmobilisation"
#

