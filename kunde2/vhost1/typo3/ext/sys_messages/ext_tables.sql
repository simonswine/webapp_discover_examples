#
# Table structure for table 'sys_messages'
#
CREATE TABLE sys_messages (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  title tinytext NOT NULL,
  note text NOT NULL,
  parent int(11) unsigned DEFAULT '0' NOT NULL,
  orig_recipient tinyint(4) DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY cruser_id (cruser_id),
  KEY parent (pid)
);

#
# Table structure for table 'sys_messages_users_mm'
#
CREATE TABLE sys_messages_users_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  status tinyint(4) DEFAULT '0' NOT NULL,
  is_read tinyint(4) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  mm_uid int(11) DEFAULT '0' NOT NULL auto_increment,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign),
  PRIMARY KEY (mm_uid)
);

