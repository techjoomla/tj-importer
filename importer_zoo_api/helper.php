<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Importer
 *
 * @copyright   Copyright (C) 2016 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die( 'Restricted access' );

// Load ZOO config
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/framework/classes/table.php';

/**
 * Importer Plugin.
 *
 * @since  3.0
 */
class ZooApiHelper extends AppTable
{
	/**
	 * Constructor
	 * 
	 * @since  3.0
	 */
	public function __construct()
	{
		$this->app			= JFactory::getApplication();
		$this->database		= JFactory::getDBO();
		$this->zapp			= App::getInstance('zoo');
	}

	/**
	 * getByAliases function
	 *
	 * @param   Array  $aliases  A array of aliases
	 * 
	 * @return  Object  object of records
	 * 
	 * @since  3.0
	 **/
	public function getByAliases($aliases)
	{
		$aliases = (array) $aliases;

		if (empty($aliases))
		{
			return array();
		}

		$strAliases = "'" . implode("','", $aliases) . "'";

		$query = $this->database->getQuery(true);

		$query->select('*')
				->from('#__zoo_item')
				->where('alias IN (' . $strAliases . ')')
				->order('FIELD (alias, ' . $strAliases . ' )');

		return $this->zapp->table->item->_queryObjectList($query);
	}

	/**
	 * Function to insert record to process images resize in resize table.
	 *
	 * @param   Integer  $batch_id  Batch Id.
	 * @param   Integer  $item_id   Record Id.
	 *
	 * @return  return $cat->id updated cat id
	 *
	 * @since   1.0.0
	 */

	public function insertResizeImageRecord($batch_id, $item_id)
	{
		return true;
	}
}
