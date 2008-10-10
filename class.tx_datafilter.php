<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Francois Suter (Cobweb) <typo3@cobweb.ch>
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
*
* $Id$
***************************************************************/

require_once(t3lib_extMgm::extPath('basecontroller', 'services/class.tx_basecontroller_filterbase.php'));

/**
 * Data Filter service for the 'datafilter' extension.
 *
 * @author	Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package	TYPO3
 * @subpackage	tx_datafilter
 */
class tx_datafilter extends tx_basecontroller_filterbase {

// Data Filter interface methods

	/**
	 * This method processes the Data Filter's configuration and returns the filter structure
	 *
	 * @return	array	standardised filter structure
	 */
	public function getFilter() {
			// Initialise the filter structure, if not defined yet
		if (!isset($this->filter)) {
			$this->filter = array('filters' => array(), 'logicalOperator' => 'AND', 'limit' => array(), 'orderby' => array(), 'rawSQL' => '');
		}

			// Handle all parts of the filter configuration
		$this->defineFilterConfiguration($this->filterData['configuration']);
		$this->filter['logicalOperator'] = $this->filterData['logical_operator'];
		$this->defineLimit($this->filterData['limit_start'], $this->filterData['limit_offset']);
		$this->defineSorting($this->filterData['orderby']);
		if (!empty($this->filterData['additional_sql'])) {
			$this->filter['rawSQL'] = $this->filterData['additional_sql'];
		}

		return $this->filter;
	}

	/**
	 * This method takes the main filter configuration and assembles the "filters" part of the structure
	 *
	 * @param	string	$configuration: filter configuration as stored in the DB record
	 * @return	void
	 */
	protected function defineFilterConfiguration($configuration) {
			// Split the configuration into individual lines
		$configurationItems = $this->parseConfiguration($configuration);
		foreach ($configurationItems as $index => $line) {
			$matches = preg_split('/\s/', $line, -1, PREG_SPLIT_NO_EMPTY);
			$fullField = array_shift($matches);
			if (strpos($fullField, '.') === false) {
				$table = '';
				$field = trim($fullField);
			}
			else {
				list($table, $field) = t3lib_div::trimExplode('.', $fullField);
			}
				// Table name name may need to be interpreted
				// This is the case when the table name is stored within curly braces
			if (!empty($table) && strpos($table, '{') === 0) {
				try {
					$table = $this->evaluateExpression(substr($table, 1, strlen($table) -2));
				}
					// The table name could not be evaluated, just set it to blank
				catch (Exception $e) {
					$table = '';
				}
			}
				// Field name name may need to be interpreted too
			if (strpos($field, '{') === 0) {
				try {
					$field = $this->evaluateExpression(substr($field, 1, strlen($field) -2));
				}
					// The field name could not be evaluated, skip to next value
				catch (Exception $e) {
					continue;
				}
			}
			$operator = strtolower(array_shift($matches));
			$valueExpression = implode(' ', $matches);
			try {
				$value = $this->evaluateExpression($valueExpression);
					// If the value is an array, check that operator is able to handle multiple values
					// Only "in", "andgroup" and "orgroup" can do that. If the operator is not one of these, switch it to "in"
					// Values from the array are simple concatenated with a comma
				if (is_array($value)) {
					if ($operator != 'andgroup' && $operator != 'orgroup' && $operator != 'in') {
						$operator = 'in';
					}
					$value = implode(',', $value);
					$filterConfiguration = array('table' => $table, 'field' => $field, 'conditions' => array(0 => array('operator' => $operator, 'value' => $value)));
					$this->filter['filters'][$index] = $filterConfiguration;
				}
					// The value is not an array and is not an empty string either
				elseif ($value !== '') {
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
							}
							else {
								$operator = '>=';
							}
							$conditions[] = array('operator' => $operator, 'value' => $lowerBoundary);
						}
							// Handle upper boundary, only if it's not * (= +infinity)
						if ($upperBoundary != '*') {
							if ($closingBracket == '[') {
								$operator = '<';
							}
							else {
								$operator = '<=';
							}
							$conditions[] = array('operator' => $operator, 'value' => $upperBoundary);
						}
					}
					else {
						$conditions = array(0 => array('operator' => $operator, 'value' => $value));
					}
					$filterConfiguration = array('table' => $table, 'field' => $field, 'conditions' => $conditions);
					$this->filter['filters'][$index] = $filterConfiguration;
				}
			}
				// The value could not be evaluated, skip to next value
			catch (Exception $e) {
				continue;
			}
		}
	}

	/**
	 * This method takes the 2 parameters of the limit configuration and assembles the "limit" part of the structure
	 *
	 * @param	string	$maxConfiguration: definition of the maximum number of records to display at a time
	 * @param	string	$offsetConfiguration: definition of the offset, as a multiplier of $max
	 * @return	void
	 */
	protected function defineLimit($maxConfiguration, $offsetConfiguration) {
		if (empty($maxConfiguration)) {
			$max = 0;
		}
		else {
			try {
				$max = $this->evaluateExpression($maxConfiguration);
				if (empty($offsetConfiguration)) {
					$offset = 0;
				}
				else {
					try {
						$offset = $this->evaluateExpression($offsetConfiguration);
					}
						// If offset expression could not be evaluated, default to 0
					catch (Exception $e) {
						$offset = 0;
					}
				}
			}
				// Do nothing special about exception, but exit process
			catch (Exception $e) {
				return;
			}
		}
		$this->filter['limit'] = array('max' => $max, 'offset' => $offset);
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
		$configurationItems = $this->parseConfiguration($orderConfiguration);
		$configFlag = 1;
		$items = array();
			// In a first pass, we store all the configuration items as we go along,
			// storing their type and value
		foreach ($configurationItems as $line) {
			$matches = t3lib_div::trimExplode('=', $line, 1);
			$items[] = array('type' => $matches[0], 'value' => $matches[1]);
		}
		$numItems = count($items);
		for ($i = 0; $i < $numItems; $i++) {
				// Consider the item only if it's a field
				// (if it's an order, it will just skip to the next item)
			if ($items[$i]['type'] == 'field') {
				$fullField = $this->evaluateExpression($items[$i]['value']);
				if (strpos($fullField, '.') === false) {
					$table = '';
					$field = $fullField;
				}
				else {
					list($table, $field) = t3lib_div::trimExplode('.', $fullField);
				}
					// Check if the next item is an order
					// If yes, take it and increase counter by 1 for stepping to the item after
				if (isset($items[$i + 1])) {
					if ($items[$i + 1]['type'] == 'order') {
						$order = $this->evaluateExpression($items[$i + 1]['value']);
						$i++;
					}
					else {
						$order = 'ASC'; // Default sorting
					}
				}
				else {
					$order = 'ASC'; // Default sorting
				}
				$this->filter['orderby'][$i] = array('table' => $table, 'field' => $field, 'order' => $order);
			}
		}
	}

	/**
	 * This method evaluates the value of a given expression for a filter
	 * The expected syntax of a filter value is key:index1|index2|...
	 * Simple values will be used as is
	 *
	 * @param	string	$expression: the expression to evaluate
	 * @return	string	The value for the filter
	 */
	protected function evaluateExpression($expression) {
		if (empty($expression)) {
			throw new Exception('Empty filter expression received');
		}
		else {
				// An expression may contain several expressions as alternate values, separated by a double slash (//)
			$allExpressions = t3lib_div::trimExplode('//', $expression);
			foreach ($allExpressions as $anExpression) {
					// If there's no colon (:) in the expression, take it to be a litteral value and return it as is
				if (strpos($anExpression, ':') === false) {
					return $anExpression;
				}
				else {
					list($key, $indices) = t3lib_div::trimExplode(':', $anExpression);
					$key = strtolower($key);
					switch ($key) {
						case 'tsfe':
							try {
								$value = $this->getValue($GLOBALS['TSFE'], $indices);
								return $value;
							}
							catch (Exception $e) {
								continue;
							}
							break;
						case 'page':
							try {
								$value = $this->getValue($GLOBALS['TSFE']->page, $indices);
								return $value;
							}
							catch (Exception $e) {
								continue;
							}
							break;
						case 'gp':
							try {
								$value = $this->getValue(array_merge(t3lib_div::_GET(), t3lib_div::_POST()), $indices);
								return $value;
							}
							catch (Exception $e) {
								continue;
							}
							break;
						case 'vars':
							try {
								$value = $this->getValue($this->vars, $indices);
								return $value;
							}
							catch (Exception $e) {
								continue;
							}
							break;
						case 'date':
							return date($indices);
							break;
					}
				}
			}
		}
			// If we have come all this way and found no value, throw an exception
		throw new Exception('No value found for expression: '.$expression);
	}

	/**
	 * This method is used to get a value from inside a multi-dimensional array or object
	 * NOTE: this code is largely inspired by tslib_content::getGlobal()
	 *
	 * @param	mixed	$source: array or object to look into
	 * @param	string	$indices: "path" of indinces inside the multi-dimensional array, of the form index1|index2|...
	 * @return	mixed	Whatever value was found in the array
	 */
	protected function getValue($source, $indices) {
		if (empty($indices)) {
			throw new Exception('No key given for source');
		}
		else {
			$indexList = t3lib_div::trimExplode('|', $indices);
			$value = $source;
			foreach ($indexList as $key) {
				if (is_object($value) && isset($value->$key)) {
					$value = $value->$key;
				}
				elseif (is_array($value) && isset($value[$key])) {
					$value = $value[$key];
				}
				else {
					throw new Exception('Key '.$indices.' not found in source');
				}
			}
		}
		return $value;
	}

	/**
	 * This method reads a configuration field and returns a cleaned up set of configuration statements
	 * ignoring blank lines and comments
	 *
	 * @param	string	$text: full configuration text
	 * @return	array	List of configuration statements
	 */
	protected function parseConfiguration($text) {
		$lines = array();
			// Explode all the lines on the return character
		$allLines = t3lib_div::trimExplode("\n", $text, 1);
		foreach ($allLines as $aLine) {
				// Take only line that don't start with # or // (comments)
			if (strpos($aLine, '#') !== 0 && strpos($aLine, '//') !== 0) {
				$lines[] = $aLine;
			}
		}
		return $lines;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/datafilter/class.tx_datafilter.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/datafilter/class.tx_datafilter.php']);
}

?>