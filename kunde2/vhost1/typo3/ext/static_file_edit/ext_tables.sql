
#
# Table structure for table 'sys_staticfile_edit'
#
CREATE TABLE sys_staticfile_edit (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser int(11) unsigned DEFAULT '0' NOT NULL,
  edit_content mediumtext NOT NULL,
  edit_file tinyblob NOT NULL,
  edit_subpart_marker varchar(40) DEFAULT '' NOT NULL,
  always_reload tinyint(4) DEFAULT '0' NOT NULL,
  update_status tinytext NOT NULL,
  rte_enabled tinyint(4) DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);
