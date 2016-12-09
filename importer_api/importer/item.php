<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Importer
 *
 * @copyright   Copyright (C) 2016 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

JLoader::import('components.com_importer.models.item', JPATH_ADMINISTRATOR);
JLoader::import('components.com_importer.models.items', JPATH_ADMINISTRATOR);
JLoader::import('components.com_importer.tables.item', JPATH_ADMINISTRATOR);

/**
 * Item Resource for Importer Plugin.
 *
 * @since  2.5
 */
class ImporterApiResourceItem extends ApiResource
{
	/**
	 * GET function to get list of records in importer_item table based on batch_id
	 * 
	 * ***INPUT PARAMS***
	 * *batch_id	- mandatory
	 * *limit		- mandatory
	 * *offset		- mandatory
	 * 
	 * @return  JSON  success of failur status.
	 *
	 * @since  3.0
	 */
	public function get()
	{
		$app 			= JFactory::getApplication();
		$jinput 		= $app->input;
		$items_model 	= JModelLegacy::getInstance('items', 'ImporterModel');

		$batch_id		= $jinput->get('batch_id', '', 'INT');
		$limit			= $jinput->get('limit', '', 'INT');
		$offset			= $jinput->get('offset', '', 'INT');

		if ( ($batch_id > 0) && ($offset >= 0) && ($limit > 0) )
		{
			$items_model->setState('filter.batch_id', $batch_id);
			$items_model->setState('filter.limit', $limit);
			$items_model->setState('filter.offset', $offset);

			$importerItems	= $items_model->getItems();
		}
		else
		{
			echo "some params missing";
		}

		print_r($importerItems);
		die;
	}

	/**
	 * POST function save items in importer_items table
	 * 
	 * ***INPUT PARAMS***
	 * *batch_records		- object containing records.
	 * 
	 * @return  JSON  success of failur status.
	 *
	 * @since  3.0
	 **/
	public function post()
	{
		$this->plugin->setResponse($this->saveTemp());
		die;
	}

	/**
	 * saveTemp function to save item data in importer_records table
	 *
	 * @return  JSON  success of failur status.
	 *
	 * @since  3.0
	 **/
	public function saveTemp()
	{
		$app 			= JFactory::getApplication();
		$result 		= new stdClass;
		$item_model		= JModelLegacy::getInstance('item', 'ImporterModel');

		$postData 		= $app->input->getArray();
		$formData 		= $postData['JForm'];
		print_r($item_model->save($formData));
	}
}
