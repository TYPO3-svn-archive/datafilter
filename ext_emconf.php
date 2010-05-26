<?php

########################################################################
# Extension Manager/Repository config file for ext: "datafilter"
#
# Auto generated 03-09-2008 14:00
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Data Filter',
	'description' => 'Provides a way to create complex filters that can be passed to a Data Provider for restricting the data it returns.',
	'category' => 'fe',
	'author' => 'Francois Suter (Cobweb)',
	'author_email' => 'typo3@cobweb.ch',
	'shy' => '',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.13.0',
	'constraints' => array(
		'depends' => array(
			'tesseract' => '0.1.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:11:{s:9:"ChangeLog";s:4:"7cbf";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"c298";s:14:"ext_tables.php";s:4:"e46e";s:14:"ext_tables.sql";s:4:"3c05";s:30:"icon_tx_datafilter_filters.gif";s:4:"475a";s:16:"locallang_db.xml";s:4:"26ed";s:7:"tca.php";s:4:"fb1e";s:19:"doc/wizard_form.dat";s:4:"f615";s:20:"doc/wizard_form.html";s:4:"3878";}',
);

?>