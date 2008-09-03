<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::allowTableOnStandardPages('tx_datafilter_filters');

$TCA['tx_datafilter_filters'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:datafilter/locallang_db.xml:tx_datafilter_filters',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_datafilter_filters.gif',
	),
);

// Register datafilter as a Data Filter

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['columns']['tx_displaycontroller_datafilter']['config']['allowed'] .= ',tx_datafilter_filters';


// Add a wizard for adding a datafilter

$addDatafilteryWizard = array(
						'type' => 'script',
						'title' => 'LLL:EXT:datafilter/locallang_db.xml:wizards.add_datafilter',
						'script' => 'wizard_add.php',
						'icon' => 'EXT:datafilter/res/icons/add_datafilter_wizard.gif',
						'params' => array(
								'table' => 'tx_datafilter_filters',
								'pid' => '###CURRENT_PID###',
								'setValue' => 'append'
							)
						);
$TCA['tt_content']['columns']['tx_displaycontroller_datafilter']['config']['wizards']['add_datafilter'] = $addDatafilteryWizard;
?>