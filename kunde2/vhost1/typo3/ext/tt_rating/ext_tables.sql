#
# Table structure for table 'tt_rating'
#
CREATE TABLE tt_rating (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  title tinytext NOT NULL,
  description text NOT NULL,
  rating varchar(20) DEFAULT '0' NOT NULL,
  votes int(11) DEFAULT '0' NOT NULL,
  ratingstat tinytext NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  recordlink tinyblob NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);



