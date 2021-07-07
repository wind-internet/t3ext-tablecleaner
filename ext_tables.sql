#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_tablecleaner_exclude int(4) unsigned DEFAULT '0' NOT NULL,
	tx_tablecleaner_exclude_branch int(4) unsigned DEFAULT '0' NOT NULL,
	KEY tx_tablecleaner (tx_tablecleaner_exclude, tx_tablecleaner_exclude_branch)
);
