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
	public function setUp() {
		$_GET['tx_choice'] = array('foo', 'bar');
	}

	/**
	 * Test the simplest possible filter: an equality with a fixed value
	 *
	 * @test
	 */
	public function filterSimple() {
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
					'void' => FALSE,
					'conditions' => array(
						0 => array(
							'operator' => '=',
							'value' => '42'
						)
					),
					'string' => 'tt_content.uid = 42'
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

	/**
	 * Test a filter with array-type values
	 *
	 * @test
	 */
	public function filterWithArrayValue() {
			// Define the filter to parse
		$filterDefinition = array(
			'configuration' => 'tt_content.header like gp:tx_choice',
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
					'field' => 'header',
					'main' => FALSE,
					'void' => FALSE,
					'conditions' => array(
						0 => array(
							'operator' => 'like',
							'value' => array(
								'foo',
								'bar'
							)
						)
					),
					'string' => 'tt_content.header like gp:tx_choice'
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
					'tt_content.header' => array(
						0 => array(
							'condition' => 'like foo,bar',
							'operator' => 'like',
							'value' => 'foo,bar'
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