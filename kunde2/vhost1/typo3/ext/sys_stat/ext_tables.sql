
#
# Table structure for table 'sys_stat'
#
CREATE TABLE sys_stat (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  page_id int(11) unsigned DEFAULT '0' NOT NULL,
  jumpurl tinytext NOT NULL,
  feuser_id int(11) unsigned DEFAULT '0' NOT NULL,
  cookie varchar(10) DEFAULT '' NOT NULL,
  IP tinytext NOT NULL,
  host tinytext NOT NULL,
  referer tinytext NOT NULL,
  browser tinytext NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  page_type tinyint(4) unsigned DEFAULT '0' NOT NULL,
  parsetime mediumint(11) unsigned DEFAULT '0' NOT NULL,
  flags tinyint(3) unsigned DEFAULT '0' NOT NULL,
  rl0 int(11) DEFAULT '0' NOT NULL,
  rl1 int(11) DEFAULT '0' NOT NULL,
  sureCookie int(11) DEFAULT '0' NOT NULL,
  client_browser varchar(5) DEFAULT '' NOT NULL,
  client_version double(16,4) DEFAULT '0.0000' NOT NULL,
  client_os varchar(4) DEFAULT '' NOT NULL,
  PRIMARY KEY (uid),
  KEY page_id (page_id,tstamp,sureCookie),
  KEY rl0 (rl0,tstamp,sureCookie),
  KEY rl1 (rl1,tstamp,sureCookie)
);
