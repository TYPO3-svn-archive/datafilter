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
* $Id: class.tx_datadisplay_pi1.php 3938 2008-06-04 08:39:01Z fsuter $
***************************************************************/

require_once(t3lib_extMgm::extPath('basecontroller', 'services/class.tx_basecontroller_filterbase.php'));

/**
 * Date Filter service for the 'datafilter' extension.
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
		$filter = array('filters' => array());

			// Get the data filter's record
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->table, "uid = '".$this->uid."'");
		if ($res) {
			$this->filterData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			if (!empty($this->filterData['configuration'])) {
					// Split the configuration into individual lines
				$configuration = t3lib_div::trimExplode("\n", $this->filterData['configuration'], 1);
				foreach ($configuration as $line) {
					$matches = preg_split('/\s/', $line, -1, PREG_SPLIT_NO_EMPTY);
					$fullField = $matches[0];
					if (strpos($fullField, '.') === false) {
						$table = '';
						$field = $fullField;
					}
					else {
						list($table, $field) = t3lib_div::trimExplode('.', $fullField);
					}
					$operator = $matches[1];
					$valueExpression = $matches[2];
					$value = $this->evaluateExpression($valueExpression);
					$filter['filters'][] = array('table' => $table, 'field' => $field, 'conditions' => array(0 => array('operator' => $operator, 'value' => $value)));
				}
			}
		}
		else {
			// An error occurred querying the database
			throw new Exception('Error getting Data Filter information');
		}
		return $filter;
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
				// If there's no colon (:) in the expression, take it to be a litteral value and return it as is
			if (strpos($expression, ':') === false) {
				return $expression;
			}
			else {
				list($key, $indices) = t3lib_div::trimExplode(':', $expression);
				$key = strtolower($key);
				switch ($key) {
					case 'tsfe':
						$value = $this->getValue($GLOBALS['TSFE'], $indices);
						break;
					case 'page':
						$value = $this->getValue($GLOBALS['TSFE']->page, $indices);
						break;
					case 'vars':
						$value = $this->getValue($this->vars, $indices);
						break;
				}
			}
		}
		return $value;
	}

	/**
	 * This method is used to get a value from inside a multi-dimensional array
	 *
	 * @param	array	$source: array to look into
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
				if (isset($value[$key])) {
					$value = $value[$key];
				}
				else {
					throw new Exception('Key '.$indices.' not found in source');
				}
			}
		}
		return $value;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/datafilter/class.tx_datafilter.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/datafilter/class.tx_datafilter.php']);
}

?>