<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2010 Francois Suter (Cobweb) <typo3@cobweb.ch>
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
 * Interface which defines the method to implement when creating a hook to post-process the "is filter empty" check
 *
 * @author		Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package		TYPO3
 * @subpackage	tx_datafilter
 *
 * $Id$
 */
interface tx_datafilter_postprocessEmptyFilterCheck {
	/**
	 * This method must be implemented for post-processing the empty filter check
	 * It receives the current status of the check and a reference to the complete filter object
	 *
	 * @param boolean $isEmpty Current value of the is filter empty flag
	 * @param tx_datafilter $filter The calling filter object
	 * @return boolean
	 */
	public function postprocessEmptyFilterCheck($isEmpty, tx_datafilter $filter);
}
?>