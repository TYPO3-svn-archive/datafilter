<?php
/*
 * Register necessary class names with autoloader
 *
 * $Id$
 */
$extensionPath = t3lib_extMgm::extPath('datafilter');
return array(
	'tx_datafilter_postprocessemptyfiltercheck'	=> $extensionPath . 'interfaces/interface.tx_datafilter_postprocessEmptyFilterCheck.php',
	'tx_datafilter_postprocessfilter'	=> $extensionPath . 'interfaces/interface.tx_datafilter_postprocessfilter.php',
	'tx_datafilter'						=> $extensionPath . 'class.tx_datafilter.php',
);
?>
