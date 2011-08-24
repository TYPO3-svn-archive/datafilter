<?php

########################################################################
# Extension Manager/Repository config file for ext "datafilter".
#
# Auto generated 24-08-2011 11:14
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Standard Data Filter - Tesseract project',
	'description' => 'Provides a way to create complex filters that can be passed to a Data Provider for restricting the data it returns. More info on http://www.typo3-tesseract.com/',
	'category' => 'fe',
	'author' => 'Francois Suter (Cobweb)',
	'author_email' => 'typo3@cobweb.ch',
	'shy' => '',
	'dependencies' => 'tesseract,expressions',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '1.2.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.3.0-0.0.0',
			'tesseract' => '1.0.0-0.0.0',
			'expressions' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:19:{s:9:"ChangeLog";s:4:"97ff";s:10:"README.txt";s:4:"3afe";s:23:"class.tx_datafilter.php";s:4:"cb69";s:16:"ext_autoload.php";s:4:"e6ab";s:12:"ext_icon.gif";s:4:"bec1";s:17:"ext_localconf.php";s:4:"d3ee";s:14:"ext_tables.php";s:4:"39d1";s:14:"ext_tables.sql";s:4:"972a";s:37:"locallang_csh_txdatafilterfilters.xml";s:4:"7abd";s:16:"locallang_db.xml";s:4:"24c3";s:7:"tca.php";s:4:"0e4d";s:14:"doc/manual.pdf";s:4:"db84";s:14:"doc/manual.sxw";s:4:"99d7";s:14:"doc/manual.txt";s:4:"c347";s:66:"interfaces/interface.tx_datafilter_postprocessEmptyFilterCheck.php";s:4:"3089";s:56:"interfaces/interface.tx_datafilter_postprocessfilter.php";s:4:"05b4";s:35:"res/icons/add_datafilter_wizard.gif";s:4:"8a58";s:40:"res/icons/icon_tx_datafilter_filters.gif";s:4:"dd56";s:42:"tests/tx_datafilter_configuration_Test.php";s:4:"3666";}',
	'suggests' => array(
	),
);

?>