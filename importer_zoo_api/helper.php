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
	 * @param   Array   $aliases  A array of aliases
	 * @param   String  $type     The type name
	 *
	 * @return  Object  object of records
	 *
	 * @since  3.0
	 **/
	public function getByAliases($aliases, $type = '')
	{
		$aliases = (array) $aliases;

		if (empty($aliases))
		{
			return array();
		}

		$aliasesPipeStr = implode("|", $aliases);

		$sanatizedStr = preg_replace('/[^a-z0-9|\-]/', "-", $aliasesPipeStr);

		$aliasesSanatized = explode("|", $sanatizedStr);

		$strAliases = "'" . implode("','", $aliasesSanatized) . "'";

		$query = $this->database->getQuery(true);

		$query->select('*')
				->from('#__zoo_item')
				->where('alias IN (' . $strAliases . ')')
				->order('FIELD (alias, ' . $strAliases . ' )');

		if ($type)
		{
			$query->where('type = "' . $type . '"');
		}

		return $this->zapp->table->item->_queryObjectList($query);
	}

	/**
	 * getSuggestions function
	 *
	 * @param   Array  $aliases  A array of aliases
	 *
	 * @return  Object  object of records
	 *
	 * @since  3.0
	 **/
	public function getSuggestions($aliases)
	{
		$aliasesPipeStr = $aliases;

		$sanatizedStr = preg_replace('/[^a-z0-9|\-]/', "---", $aliasesPipeStr);

		$query = $this->database->getQuery(true);

		$query->select('alias')
				->from('#__zoo_item')
				->setLimit('20')
				->where('alias LIKE "%' . $sanatizedStr . '%"');

		$this->database->setQuery($query);

		return $this->database->loadColumn();
	}

	/**
	 * Function to insert record to process images resize in resize table.
	 *
	 * @param   Integer  $batch_id    Batch Id.
	 * @param   Integer  $item_id     Record Id.
	 * @param   Integer  $imgpresent  Image present or not.
	 *
	 * @return  return $cat->id updated cat id
	 *
	 * @since   1.0.0
	 */

	public function insertResizeImageRecord($batch_id, $item_id, $imgpresent=1)
	{
		$data			= new stdClass;
		$data->batch_id	= $batch_id;
		$data->item_id	= $item_id;

		$findQuery = $this->database->getQuery(true);
		$findQuery->select("*")->from("#__resize_image_zoo")->where('item_id = ' . $item_id . ' AND batch_id = ' . $batch_id);
		$this->database->setQuery($findQuery);
		$findQueryResult = $this->database->loadResults();

		if (empty($findQueryResult) and $imgpresent == 1)
		{
			if (!$this->database->insertObject('#__resize_image_zoo', $data, 'item_id'))
			{
				return $this->database->getErrorMsg() . 'Error occurred while inserting records into resize_image table.';
			}
			else
			{
				return true;
			}
		}

		return true;
	}
}
