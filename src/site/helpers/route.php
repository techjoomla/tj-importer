<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Import Component Route Helper.
 *
 * @since  1.5
 */
abstract class ImporterHelperRoute
{
	/**
	 * Function to get import url as per adapter passed
	 *
	 * @param   string  $adapter         name of the adapter.
	 * 
	 * @param   string  $component_name  name of component.
	 * 
	 * @param   string  $extra           extra parameter if required.
	 *
	 * @return  returrn $link
	 *
	 * @since   1.0.0
	 */
	public static function getImporterRoute($adapter = "tmtquestions", $component_name = "com_importer", $extra = "bulkimport")
	{
		// Create the link
		$link = JUri::root() . 'index.php?option=' . $component_name . '&view=import&adapter=' . $adapter . '&sel=' . $extra . '&tmpl=component';

		return $link;
	}
}
