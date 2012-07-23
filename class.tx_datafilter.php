<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2010 Francois Suter (Cobweb) <typo3@cobweb.ch>
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
 * Data Filter service for the 'datafilter' extension.
 *
 * @author		Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package		TYPO3
 * @subpackage	tx_datafilter
 *
 * $Id$
 */
class tx_datafilter extends tx_tesseract_filterbase {

// Data Filter interface methods

	/**
	 * This method processes the Data Filter's configuration and returns the filter structure
	 *
	 * @return	array	standardised filter structure
	 */
	public function getFilterStructure() {
			// Initialise the filter structure, if not defined yet
		if (!isset($this->filter)) {
			$this->reset();
		}

			// Handle all parts of the filter configuration
		$this->defineFilterConfiguration($this->filterData['configuration']);
		$this->filter['logicalOperator'] = $this->filterData['logical_operator'];
		$this->defineLimit($this->filterData['limit_start'], $this->filterData['limit_offset'], $this->filterData['limit_pointer']);
		$this->defineSorting($this->filterData['orderby']);
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['datafilter']['postprocessReturnValue'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['datafilter']['postprocessReturnValue'] as $className) {
					/** @var $postProcessor tx_datafilter_postprocessFilter */
				$postProcessor = &t3lib_div::getUserObj($className);
				if ($postProcessor instanceof tx_datafilter_postprocessFilter) {
					$postProcessor->postprocessFilter($this);
				}
			}
		}

			// Before returning, save the filter to session
		$this->saveFilter();
		return $this->filter;
	}

	/**
	 * This method returns true or false depending on whether the filter can be considered empty or not
	 *
	 * @return bool
	 */
	public function isFilterEmpty() {
		$isEmpty = parent::isFilterEmpty();
			// Call hook to extend the empty filter check
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['datafilter']['postprocessEmptyFilterCheck'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['datafilter']['postprocessEmptyFilterCheck'] as $className) {
					/** @var $postProcessor tx_datafilter_postprocessEmptyFilterCheck */
				$postProcessor = &t3lib_div::getUserObj($className);
				if ($postProcessor instanceof tx_datafilter_postprocessEmptyFilterCheck) {
					$isEmpty = $postProcessor->postprocessEmptyFilterCheck($isEmpty, $this);
				}
			}
		}
		return $isEmpty;
	}

	/**
	 * This method is used to save the filter in session
	 *
	 * @return	void
	 */
	public function saveFilter() {
		$key = '';
			// If a session key has been set and TYPO3 is running in FE mode,
			// save the filter in session
		if (!empty($this->filterData['session_key']) && TYPO3_MODE == 'FE') {
				// Assemble the key for session storage
				// It is either a general key name or a key name per page (with page id appended)
			if (empty($this->filterData['key_per_page'])) {
				$key = $this->filterData['session_key'];
			}
			else {
				$key = $this->filterData['session_key'] . '_' . $GLOBALS['TSFE']->id;
			}
				// NOTE: we save only the parsed part, as it is the only we are interested in keeping in session
			$GLOBALS['TSFE']->fe_user->setKey('ses', $key, $this->filter['parsed']);
		}
	}

// Other methods

	/**
	 * This method takes the main filter configuration and assembles the "filters" part of the structure
	 *
	 * @param	string	$configuration: filter configuration as stored in the DB record
	 * @return	void
	 */
	protected function defineFilterConfiguration($configuration) {
			// Split the configuration into individual lines
		$configurationItems = tx_tesseract_utilities::parseConfigurationField($configuration);
		foreach ($configurationItems as $index => $line) {
				// Parse the configuration line for possible subexpressions
			$parsedLine = tx_expressions_parser::evaluateString($line, false);
				// Check if the line contains an explicit naming marker (double colon)
			$theLine = $parsedLine;
			if (strpos($parsedLine, '::') !== false) {
					// The line has a naming marker
					// The part before the double colon comes as a replacement for the numeric index,
					// the part after is the configuration line itself
				list($index, $theLine) = t3lib_div::trimExplode('::', $parsedLine);
			}
			$matches = preg_split('/\s/', $theLine, -1, PREG_SPLIT_NO_EMPTY);
				// The first match is the field name, potentially prepended with the table name
			$fullField = array_shift($matches);
			$table = '';
			$field = trim($fullField);
			$mainFlag = FALSE;
			$voidFlag = FALSE;
				// The full field syntax may actually contain the "main" keyword,
				// the table name and the field name, each separated by dots (.)
			if (strpos($fullField, '.') !== false) {
				$fullFieldParts = t3lib_div::trimExplode('.', $fullField);
					// The field is always the last part
				$field = array_pop($fullFieldParts);
					// If there's only one part left, it may be either a special keyword
					// or a table's name
				if (count($fullFieldParts) == 1) {
					$part = array_pop($fullFieldParts);
					if ($part == 'main') {
						$mainFlag = TRUE;
					} elseif ($part == 'void') {
						$voidFlag = TRUE;
					} else {
						$table = $part;
					}

					// If there are more than one parts left, we expect the first part
					// to be a special keyword and the second part to be a table's name
				} else {
						// NOTE: if the part does not match a keyword, it is ignored
						// TODO: log a warning about invalid syntax
					$part = array_shift($fullFieldParts);
					if ($part == 'main') {
						$mainFlag = TRUE;
					} elseif ($part == 'void') {
						$voidFlag = TRUE;
					}
						// Get the "last" part (if it's not the last, there's a syntax error)
						// TODO: we could throw an exception in this case
					$table = array_shift($fullFieldParts);
				}
//				list($table, $field) = t3lib_div::trimExplode('.', $fullField);
			}
				// The second match is the operator
			$operator = strtolower(array_shift($matches));
				// If the operator starts with a !, it's a negation
				// Set the negation flag and strip the !
			if (strpos($operator, '!') === 0) {
				$negate = TRUE;
				$operator = substr($operator, 1);
			} else {
				$negate = FALSE;
			}
				// All the other matches are put together again to form the expression to be evaluated
			$valueExpression = implode(' ', $matches);
			try {
				$value = tx_expressions_parser::evaluateExpression($valueExpression);
					// Test special value "\clear_cache" (or its old value "clear_cache")
					// If the returned value is equal to this, it means the saved value must be removed
				if ($value == '\clear_cache' || $value == 'clear_cache') {
					unset($this->filter['filters'][$index]);
				} else {
						// If the value is an array, just use it straightaway
					if (is_array($value)) {
						$filterConfiguration = array(
							'table' => $table,
							'field' => $field,
							'main' => $mainFlag,
							'void' => $voidFlag,
							'conditions' => array(
								0 => array(
									'operator' => $operator,
									'value' => $value,
									'negate' => $negate
								)
							),
							'string' => $line
						);
						$this->filter['filters'][$index] = $filterConfiguration;
						$this->saveParsedFilter($index, $table, $field, $operator, $value, $negate);

						// The value is not an array and is not an empty string either
					} elseif ($value !== '') {
						$this->saveParsedFilter($index, $table, $field, $operator, $value, $negate);
							// If value is an interval, this requires more processing
							// The 2 boundaries of the interval must be extracted and the simple operator replaced by 2 conditions
						$matches = array();
						$matching = preg_match_all('/([\[\]])([^,]*),(\w*)([\[\]])/', $value, $matches);
							// If the expression has matched, we have an interval
						if ($matching == 1) {
							$openingBracket = $matches[1][0];
							$lowerBoundary = $matches[2][0];
							$upperBoundary = $matches[3][0];
							$closingBracket = $matches[4][0];
							$conditions = array();
								// Handle lower boundary, only if it's not * (= -infinity)
							if ($lowerBoundary != '*') {
								if ($openingBracket == ']') {
									$operator = '>';
								} else {
									$operator = '>=';
								}
								$conditions[] = array(
									'operator' => $operator,
									'value' => $lowerBoundary,
									'negate' => FALSE
								);
							}
								// Handle upper boundary, only if it's not * (= +infinity)
							if ($upperBoundary != '*') {
								if ($closingBracket == '[') {
									$operator = '<';
								} else {
									$operator = '<=';
								}
								$conditions[] = array(
									'operator' => $operator,
									'value' => $upperBoundary,
									'negate' => FALSE
								);
							}
								// If the condition contained a negation, issue warning that this cannot be used in a filter
							if ($negate) {
								$this->controller->addMessage('datfilter',
									'Negation ignored in condition: ' . $theLine,
									'Intervals cannot be negated',
									t3lib_FlashMessage::NOTICE
								);
							}

							// Normal filter, with no peculiarity, just set it
						} else {
								// If the value starts with a backslash, it's a special one
							if (strpos($value, '\\') === 0) {
									// Check as lowercase
								$lowercaseValue = strtolower($value);
								switch ($lowercaseValue) {
									case '\empty':
										$value = '\empty';
										break;
									case '\null';
										$value = '\null';
										break;
									case '\all':
										$value = '\all';
										break;
								}
							}
							$conditions = array(
								0 => array(
									'operator' => $operator,
									'value' => $value,
									'negate' => $negate
								)
							);
						}
						$filterConfiguration = array(
							'table' => $table,
							'field' => $field,
							'main' => $mainFlag,
							'void' => $voidFlag,
							'conditions' => $conditions,
							'string' => $line
						);
						$this->filter['filters'][$index] = $filterConfiguration;
					}
				}
			}
				// The value could not be evaluated, skip to next value
			catch (Exception $e) {
				continue;
			}
		}
	}

	/**
	 * This method takes the 3 parameters of the limit configuration and assembles the "limit" part of the structure
	 *
	 * @param	string	$maxConfiguration: definition of the maximum number of records to display at a time
	 * @param	string	$offsetConfiguration: definition of the offset, as a multiplier of $max
	 * @param	string	$pointerConfiguration: definintion of the direct pointer to a specific item
	 * @return	void
	 */
	protected function defineLimit($maxConfiguration, $offsetConfiguration, $pointerConfiguration) {
		$max = 0;
		$offset = 0;
		$pointer = 0;
		if (!empty($maxConfiguration)) {
			try {
				$max = tx_expressions_parser::evaluateExpression($maxConfiguration);
				if (empty($offsetConfiguration)) {
					$offset = 0;
				}
				else {
					try {
						$offset = tx_expressions_parser::evaluateExpression($offsetConfiguration);
					}
						// If offset expression could not be evaluated, default to 0
					catch (Exception $e) {
						$offset = 0;
					}
				}
				if (empty($pointerConfiguration)) {
					$pointer = 0;
				}
				else {
					try {
						$pointer = tx_expressions_parser::evaluateExpression($pointerConfiguration);

					}
						// If startitem expression could not be evaluated, default to 0
					catch (Exception $e) {
						$pointer = 0;
					}
				}
			}
				// Do nothing special about exception, but exit process
			catch (Exception $e) {
				return;
			}
		}
		$this->filter['limit'] = array('max' => $max, 'offset' => $offset, 'pointer' => $pointer);
	}

	/**
	 * This method takes the order by configuration and assembles the "orderby" part of the structure
	 *
	 * @param	string	$orderConfiguration: order by configuration, as stored in the DB
	 * @return	void
	 */
	protected function defineSorting($orderConfiguration) {
		if (empty($orderConfiguration)) {
			return;
		}
			// Split the configuration into individual lines
		$configurationItems = tx_tesseract_utilities::parseConfigurationField($orderConfiguration);
		$items = array();
			// In a first pass, we store all the configuration items as we go along,
			// storing their type and value
		$hasRandomOrdering = FALSE;
		foreach ($configurationItems as $line) {
				// If the special \rand keyword (for random ordering) is used on any line, mark it as such
				// and interrupt the process
			if (strpos($line, '\rand') !== FALSE) {
				$hasRandomOrdering = TRUE;
				break;
			} else {
				$matches = t3lib_div::trimExplode('=', $line, 1);
				$items[] = array('type' => $matches[0], 'value' => $matches[1]);
			}
		}
			// If the ordering structure contains at least one random ordering statement,
			// consider only this, as any other ordering wouldn't make any sense
		if ($hasRandomOrdering) {
				// Note: since it is the only ordering configuration, it always has index = 1
			$this->filter['orderby'][1] = array(
				'table' => '',
				'field' => '',
				'order' => 'RAND',
				'engine' => ''
			);

			// Otherwise parse the ordering statements line by line, expecting first a "field" directive
			// and then either "order" or "engine", or another "field" which indicates the start of a new
			// ordering configuration. Lines coming out of step are ignored.
		} else {
			$numItems = count($items);
			$lineCounter = 0;
			$newConfiguration = FALSE;
			$configurations = array();
			$currentConfiguration = 0;
			$parseErrors = array();

			do {

					// If we don't have a configuration yet, the only acceptable type is "field"
				if (count($configurations) == 0 && $items[$lineCounter]['type'] != 'field') {
					$parseErrors[] = 'Expected "field" property on line: ' . htmlspecialchars(implode(' ', $items[$lineCounter]));

					// If we have a "field" entry, it's a new configuration, set it up with default values
				} elseif (!$newConfiguration && $items[$lineCounter]['type'] == 'field') {
					$newConfiguration = TRUE;
					$fullField = tx_expressions_parser::evaluateString($items[$lineCounter]['value']);
					$table = '';
					$field = $fullField;
					if (strpos($fullField, '.') !== FALSE) {
						list($table, $field) = t3lib_div::trimExplode('.', $fullField);
					}
					$currentConfiguration = $lineCounter;
					$configurations[$currentConfiguration] = array(
						'table' => $table,
						'field' => $field,
						'order' => 'ASC',
						'engine' => ''
					);

					// Currently handling a configuration, add information if appropriate types are provided
				} else {
					$newConfiguration = FALSE;
					switch ($items[$lineCounter]['type']) {
						case 'order':
							$order = tx_expressions_parser::evaluateString($items[$lineCounter]['value']);
							$configurations[$currentConfiguration]['order'] = $order;
							break;
						case 'engine':
							$engine = strtolower(tx_expressions_parser::evaluateString($items[$lineCounter]['value']));
								// Ensure valid value
							if (!in_array($engine, self::$allowedOrderingEngines)) {
								$engine = '';
							}
							$configurations[$currentConfiguration]['engine'] = $engine;
							break;
						default:
							$parseErrors[] = 'Invalid ordering configuration on line: ' . htmlspecialchars(implode(' = ', $items[$lineCounter]));
					}
				}
				$lineCounter++;

			} while ($lineCounter < $numItems);
				// Set the found configurations
			$this->filter['orderby'] = $configurations;
				// Report parse errors, if any
			if (count($parseErrors) > 0) {
				$this->controller->addMessage(
					'datafilter',
					'<ul><li>' . implode('</li><li>', $parseErrors) . '</li></ul>',
					'Invalid configuration items for ordering',
					t3lib_FlashMessage::WARNING
				);
			}
		}
	}

	/**
	 * This method takes all the parameters of a filter and stores them in the "parsed" section
	 * of the filter. This section contains all filters stored in a special way, i.e. the table and field
	 * are used as key and the operator and value become the value.
	 * This syntax makes it possible to easily retrieve filter configurations when using the "session:" key in the
	 * expression parser
	 *
	 * @param mixed	$index Number or string used as a key for the given configuration
	 * @param string $table Name of the table the filter applies to
	 * @param string $field Name of the field the filter applies to
	 * @param string $operator The operator of the condition
	 * @param string $value The value of the condition
	 * @param bool $negate TRUE if the operator is negated, FALSE otherwise
	 * @return void
	 */
	protected function saveParsedFilter($index, $table, $field, $operator, $value, $negate) {
			// Assemble storage key
		$keyForStorage = (empty($table)) ? '' : $table . '.';
		$keyForStorage .= $field;
			// Initialize storage, if necessary
		if (!isset($this->filter['parsed']['filters'][$keyForStorage])) {
			$this->filter['parsed']['filters'][$keyForStorage] = array();
		}
			// Compute values to store
			// If the value is an array, it is turned into a comma-separated string
			// NOTE: this will obviously fail with multidimensional arrays, but the alternative is to serialize
			// the value. This doesn't seem like a useful thing to do, since the values stored here are supposed
			// to be retrievable by the filters themselves, which won't be able to handle serialized values.
			// Thus the limitation to 1-dimensional arrays seems reasonable
		if (is_array($value)) {
			$value = implode(',', $value);
		}
		$condition = (($negate) ? '!' : '') . $operator . ' ' . $value;
		$this->filter['parsed']['filters'][$keyForStorage][$index] = array(
			'condition' => $condition,
			'operator' => $operator,
			'value' => $value,
			'negate' => $negate
		);
	}

	/**
	 * This method performs necessary initialisations when an instance of this service
	 * is called up several times
	 *
	 * @return	void
	 */
	public function reset() {
		$this->filter = array(
			'filters' => array(),
			'logicalOperator' => 'AND',
			'limit' => array(),
			'orderby' => array(),
			'parsed' => array(
				'filters' => array()
			)
		);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/datafilter/class.tx_datafilter.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/datafilter/class.tx_datafilter.php']);
}

?>
