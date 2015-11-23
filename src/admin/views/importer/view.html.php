<?php
/**
 * @package     Joomla.osian
 * @subpackage  com_osian
 *
 * @copyright   Copyright (C) 2013 - 2014 TWS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * TMT controller Class.
 *
 * @since  1.0
 */
class ImporterViewimporter extends JViewLegacy
{
	/**
		* Method to display the view
		*
		* @access    public
		*
		* @return nothing
		*/
	public function display()
	{
		parent::display($tpl);
	}
}
