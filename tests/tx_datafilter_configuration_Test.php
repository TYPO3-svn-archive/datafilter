<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010-2012 Francois Suter <typo3@cobweb.ch>
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
	 * Provides configurations and the expected result for testing filters
	 *
	 * @return array
	 */
	public function configurationProvider() {
		$configurations = array(
			'equality on main (with alternative values, multiline inc. comments, filter key)' => array(
				'definition' => array(
					'configuration' => "main.tt_content.uid   = gp:unknown // 42\n#tt_content.uid > 10\nhead :: tt_content.header start foo",
					'logical_operator' => 'AND'
				),
				'result' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'uid',
							'main' => TRUE,
							'void' => FALSE,
							'conditions' => array(
								0 => array(
									'operator' => '=',
									'value' => '42',
									'negate' => FALSE
								)
							),
							'string' => 'main.tt_content.uid   = gp:unknown // 42'
						),
						'head' => array(
							'table' => 'tt_content',
							'field' => 'header',
							'main' => FALSE,
							'void' => FALSE,
							'conditions' => array(
								0 => array(
									'operator' => 'start',
									'value' => 'foo',
									'negate' => FALSE
								)
							),
							'string' => 'head :: tt_content.header start foo'
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
									'value' => '42',
									'negate' => FALSE
								)
							),
							'tt_content.header' => array(
								'head' => array(
									'condition' => 'start foo',
									'operator' => 'start',
									'value' => 'foo',
									'negate' => FALSE
								)
							)
						)
					)
				),
			),
			'in interval' => array(
				'definition' => array(
					'configuration' => 'tt_content.uid = [100,200]',
					'logical_operator' => 'AND'
				),
				'result' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'uid',
							'main' => FALSE,
							'void' => FALSE,
							'conditions' => array(
								0 => array(
									'operator' => '>=',
									'value' => '100',
									'negate' => FALSE
								),
								1 => array(
									'operator' => '<=',
									'value' => '200',
									'negate' => FALSE
								)
							),
							'string' => 'tt_content.uid = [100,200]'
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
									'condition' => '= [100,200]',
									'operator' => '=',
									'value' => '[100,200]',
									'negate' => FALSE
								)
							)
						)
					)
				),
			),
			'not null' => array(
				'definition' => array(
					'configuration' => 'tt_content.image != \NULL',
					'logical_operator' => 'AND'
				),
				'result' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'image',
							'main' => FALSE,
							'void' => FALSE,
							'conditions' => array(
								0 => array(
									'operator' => '=',
									'value' => '\null',
									'negate' => TRUE
								)
							),
							'string' => 'tt_content.image != \NULL'
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
							'tt_content.image' => array(
								0 => array(
									'condition' => '!= \NULL',
									'operator' => '=',
									'value' => '\NULL',
									'negate' => TRUE
								)
							)
						)
					)
				),
			),
			'array of gp values' => array(
				'definition' => array(
					'configuration' => 'tt_content.header like gp:tx_choice',
					'logical_operator' => 'AND'
				),
				'result' => array(
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
									),
									'negate' => FALSE
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
									'value' => 'foo,bar',
									'negate' => FALSE
								)
							)
						)
					)
				)
			),
			'date with void and OR' => array(
				'definition' => array(
					'configuration' => 'void.tt_content.tstamp > strtotime:2010-01-01',
					'logical_operator' => 'OR'
				),
				'result' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'tstamp',
							'main' => FALSE,
							'void' => TRUE,
							'conditions' => array(
								0 => array(
									'operator' => '>',
									'value' => '1262300400',
									'negate' => FALSE
								)
							),
							'string' => 'void.tt_content.tstamp > strtotime:2010-01-01'
						)
					),
					'logicalOperator' => 'OR',
					'limit' => array(
						'max' => 0,
						'offset' => 0,
						'pointer' => 0
					),
					'orderby' => array(),
					'parsed' => array(
						'filters' => array(
							'tt_content.tstamp' => array(
								0 => array(
									'condition' => '> 1262300400',
									'operator' => '>',
									'value' => '1262300400',
									'negate' => FALSE
								)
							)
						)
					)
				),
			),
			'limits and order' => array(
				'definition' => array(
					'configuration' => '',
					'logical_operator' => 'AND',
					'orderby' => "field = tt_content.tstamp\norder = desc\nfield = tt_content.starttime\norder=ASC\nengine=source",
					'limit_start' => 'gp:page // 0',
					'limit_offset' => 'gp:max // 20',
					'limit_pointer' => ''
				),
				'result' => array(
					'filters' => array(),
					'logicalOperator' => 'AND',
					'limit' => array(
						'max' => 0,
						'offset' => 20,
						'pointer' => 0
					),
					'orderby' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'tstamp',
							'order' => 'desc',
							'engine' => ''
						),
						2 => array(
							'table' => 'tt_content',
							'field' => 'starttime',
							'order' => 'ASC',
							'engine' => 'source'
						)
					),
					'parsed' => array(
						'filters' => array()
					)
				),
			),
				// Ordering configuration with errors or some weirdness:
				// - first line is skipped because we don't have a "field" yet
				// - empty line after field is removed entirely
				// - so is line with comment
				// - second ordering for first field overrides first ordering
				// - engine value for the second field is invalid
			'ordering (unusual or bad configuration)' => array(
				'definition' => array(
					'configuration' => '',
					'logical_operator' => 'AND',
					'orderby' => "order = foo\nfield = tt_content.tstamp\n\n# Comment\norder = desc\norder = asc\nengine = source\nfield = tt_content.starttime\norder=foo\nengine = bar",
					'limit_start' => '',
					'limit_offset' => '',
					'limit_pointer' => ''
				),
				'result' => array(
					'filters' => array(),
					'logicalOperator' => 'AND',
					'limit' => array(
						'max' => 0,
						'offset' => 0,
						'pointer' => 0
					),
					'orderby' => array(
						1 => array(
							'table' => 'tt_content',
							'field' => 'tstamp',
							'order' => 'asc',
							'engine' => 'source'
						),
						5 => array(
							'table' => 'tt_content',
							'field' => 'starttime',
							'order' => 'foo',
							'engine' => ''
						)
					),
					'parsed' => array(
						'filters' => array()
					)
				),
			),
			'random ordering (clean definition)' => array(
				'definition' => array(
					'configuration' => '',
					'logical_operator' => 'AND',
					'orderby' => '\rand',
				),
				'result' => array(
					'filters' => array(),
					'logicalOperator' => 'AND',
					'limit' => array(
						'max' => 0,
						'offset' => 0,
						'pointer' => 0
					),
					'orderby' => array(
						1 => array(
							'table' => '',
							'field' => '',
							'order' => 'RAND',
							'engine' => ''
						)
					),
					'parsed' => array(
						'filters' => array()
					)
				),
			),
			'random ordering (not clean, but still valid definition)' => array(
				'definition' => array(
					'configuration' => '',
					'logical_operator' => 'AND',
					'orderby' => 'field = \rand',
				),
				'result' => array(
					'filters' => array(),
					'logicalOperator' => 'AND',
					'limit' => array(
						'max' => 0,
						'offset' => 0,
						'pointer' => 0
					),
					'orderby' => array(
						1 => array(
							'table' => '',
							'field' => '',
							'order' => 'RAND',
							'engine' => ''
						)
					),
					'parsed' => array(
						'filters' => array()
					)
				),
			)
		);
		return $configurations;
	}

	/**
	 * Test the parsing various filters
	 *
	 * @param string $definition The raw filter definition
	 * @param array $result The expected structure of the parsed filter
	 * @test
	 * @dataProvider configurationProvider
	 */
	public function testFilters($definition, $result) {
		/** @var tx_datafilter	$filterObject */
		$filterObject = t3lib_div::makeInstance('tx_datafilter');
		/** @var $controller tx_tesseract_picontrollerbase */
		$controller = $this->getMock('tx_tesseract_picontrollerbase');
		$filterObject->setController($controller);
		$filterObject->setData($definition);
		$actualResult = $filterObject->getFilterStructure();
			// Check if the "structure" part if correct
		$this->assertEquals($result, $actualResult);
	}
}
?>