CREATE TABLE IF NOT EXISTS module_version_tracker (
  ID int(10) NOT NULL auto_increment,
  module_code varchar(255) NOT NULL,
  patch_level int(10) NOT NULL,
  version_name varchar(255),
  PRIMARY KEY  (ID),
  UNIQUE (module_code) 
) ENGINE=MyISAM;