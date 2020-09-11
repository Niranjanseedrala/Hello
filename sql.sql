CREATE TABLE IF NOT EXISTS `s178_guestbook` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default '',
  `email` varchar(255) default '',
  `description` varchar(255) default '',
  `when` int(11) NOT NULL default '0',
  `ip` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
