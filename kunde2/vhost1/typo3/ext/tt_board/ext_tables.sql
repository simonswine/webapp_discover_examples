#
# Table structure for table 'tt_board'
#
CREATE TABLE tt_board (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  author varchar(80) DEFAULT '' NOT NULL,
  email varchar(80) DEFAULT '' NOT NULL,
  subject tinytext NOT NULL,
  message text NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  parent int(10) unsigned DEFAULT '0' NOT NULL,
  notify_me tinyint(3) unsigned DEFAULT '0' NOT NULL,
  doublePostCheck int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY parent_select (pid,parent)
);
