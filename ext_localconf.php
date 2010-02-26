<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

// Register as Data Provider service
// Note that the subtype corresponds to the name of the database table

t3lib_extMgm::addService($_EXTKEY,  'datafilter' /* sv type */,  'tx_datafilter' /* sv key */,
		array(

			'title' => 'Data Filter',
			'description' => 'Standard Data Filter',

			'subtype' => 'tx_datafilter_filters',

			'available' => TRUE,
			'priority' => 50,
			'quality' => 50,

			'os' => '',
			'exec' => '',

			'classFile' => t3lib_extMgm::extPath($_EXTKEY, 'class.tx_datafilter.php'),
			'className' => 'tx_datafilter',
		)
	);
?>