<?php
/**
* @package	API
* @version 0.1
* @author 	TechJoomla
* @link 	http://www.techjoomla.com
* @copyright Copyright (C) 2011 Edge Web Works, LLC. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

JLoader::import('components.com_importer.models.item', JPATH_ADMINISTRATOR);
JLoader::import('components.com_importer.models.items', JPATH_ADMINISTRATOR);
JLoader::import('components.com_importer.tables.item', JPATH_ADMINISTRATOR);

class ImporterApiResourceItem extends ApiResource
{
	/*
	 * GET function to get list of records in importer_item table based on batch_id
	 * 
	 * ***INPUT PARAMS***
	 * *batch_id	- mandatory
	 * *limit		- mandatory
	 * *offset		- mandatory
	 */
	public function get()
	{
		$app 			= JFactory::getApplication();
		$jinput 		= $app->input;
		$items_model 	= JModelLegacy::getInstance('items', 'ImporterModel');

		$batch_id		= $jinput->get('batch_id', '', 'INT');
		$limit			= $jinput->get('limit', '', 'INT');
		$offset			= $jinput->get('offset', '', 'INT');

		if( ($batch_id > 0) && ($offset >= 0) && ($limit > 0) )
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

		echo "<pre>";
		print_r($importerItems);
		die;
		
	}

	/*
	 * POST function save items in importer_items table
	 * 
	 * ***INPUT PARAMS***
	 * *batch_records		- object containing records.
	 */
	public function post()
	{
		$this->plugin->setResponse($this->saveTemp());
		die;
	}

	/*
	 * saveTemp function
	 * called from post method to call model save function to save items in importer_items table
	 * 
	 */
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
