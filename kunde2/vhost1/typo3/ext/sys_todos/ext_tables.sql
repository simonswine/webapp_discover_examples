#
# Table structure for table 'sys_todos'
#
CREATE TABLE sys_todos (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
  type varchar(11) DEFAULT '' NOT NULL,
  deadline int(11) unsigned DEFAULT '0' NOT NULL,
  finished tinyint(3) unsigned DEFAULT '0' NOT NULL,
  title tinytext NOT NULL,
  description text NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY cruser_id (cruser_id)
);

#
# Table structure for table 'sys_todos_users_mm'
#
CREATE TABLE sys_todos_users_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  status tinyint(4) DEFAULT '0' NOT NULL,
  status_log mediumblob NOT NULL,
  is_read tinyint(4) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  mm_uid int(11) DEFAULT '0' NOT NULL auto_increment,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  rec_reference varchar(50) DEFAULT '' NOT NULL,
  finalized tinyint(4) DEFAULT '0' NOT NULL,
  finished_instance tinyint(4) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign),
  PRIMARY KEY (mm_uid)
);

