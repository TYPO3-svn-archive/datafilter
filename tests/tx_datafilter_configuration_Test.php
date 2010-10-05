<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Francois Suter <typo3@cobweb.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Testcase for the Data Query SQL parser
 *
 * @author		Francois Suter <typo3@cobweb.ch>
 * @package		TYPO3
 * @subpackage	tx_datafilter
 *
 * $Id$
 */
class tx_datafilter_configuration_Test extends tx_phpunit_testcase {

	/**
	 * Test the simplest possible filter: an equality with a fixed value
	 *
	 * @test
	 */
	public function simpleFilter() {
			// Define the filter to parse
		$filterDefinition = array(
			'configuration' => 'tt_content.uid = 42',
			'logical_operator' => 'AND'
		);
		/**
		 * @var tx_datafilter	$filterObject
		 */
		$filterObject = t3lib_div::makeInstance('tx_datafilter');
		$filterObject->setData($filterDefinition);
		$actualResult = $filterObject->getFilterStructure();
			// Define the expected result
		$expectedResult = array(
			'filters' => array(
				0 => array(
					'table' => 'tt_content',
					'field' => 'uid',
					'main' => FALSE,
					'conditions' => array(
						0 => array(
							'operator' => '=',
							'value' => '42'
						)
					)
				)
			),
			'logicalOperator' => 'AND',
			'limit' => array(
				'max' => 0,
				'offset' => 0,
				'pointer' => 0
			),
			'orderby' => array(),
			'parsed' => array(
				'filters' => array(
					'tt_content.uid' => array(
						0 => array(
							'condition' => '= 42',
							'operator' => '=',
							'value' => '42'
						)
					)
				)
			)
		);
			// Check if the "structure" part if correct
		$this->assertEquals($expectedResult, $actualResult);
	}
}
?>