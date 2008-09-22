#
# Table structure for table 'tx_datafilter_filters'
#
CREATE TABLE tx_datafilter_filters (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	configuration text,
	orderby text,
	limit_start varchar(255) DEFAULT '' NOT NULL,
	limit_offset varchar(255) DEFAULT '' NOT NULL,
	additional_sql text,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);
