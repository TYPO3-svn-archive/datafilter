<?php
/* 
 * Register necessary class names with autoloader
 *
 * $Id$
 */
$extensionPath = t3lib_extMgm::extPath('datafilter');
return array(
	'tx_datafilter_postprocessfilter'	=> $extensionPath . 'interfaces/class.tx_datafilter_postprocessfilter.php',
	'tx_datafilter'						=> $extensionPath . 'class.tx_datafilter.php',
);
?>
