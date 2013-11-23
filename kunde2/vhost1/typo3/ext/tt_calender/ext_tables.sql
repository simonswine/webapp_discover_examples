#
# Table structure for table 'tt_calender'
#
CREATE TABLE tt_calender (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  type tinyint(4) unsigned DEFAULT '0' NOT NULL,
  title tinytext NOT NULL,
  note text NOT NULL,
  date int(11) unsigned DEFAULT '0' NOT NULL,
  datetext varchar(80) DEFAULT '' NOT NULL,
  week tinyint(4) unsigned DEFAULT '0' NOT NULL,
  complete tinyint(4) unsigned DEFAULT '0' NOT NULL,
  workgroup varchar(80) DEFAULT '' NOT NULL,
  responsible varchar(20) DEFAULT '' NOT NULL,
  category int(11) unsigned DEFAULT '0' NOT NULL,
  priority tinyint(4) unsigned DEFAULT '0' NOT NULL,
  link tinytext NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  time int(10) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'tt_calender_cat'
#
CREATE TABLE tt_calender_cat (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  title tinytext NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);
