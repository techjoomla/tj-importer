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

		$limit			= $jinput->get('limit', 20, 'INT');
		$offset			= $jinput->get('offset', 0, 'INT');

		if ( ($batch_id > 0) && ($offset >= 0) && $limit > 0)
		{
			$items_model->setState('filter.batch_id', $batch_id);
			$items_model->setState('filter.limit', $limit);
			$items_model->setState('filter.offset', $offset);

			$importerItems	= $items_model->getItems();
			$importerItemsTotal	= $items_model->getTotal();
		}
		else
		{
			$items_model->setState('filter.batch_id', $batch_id);
			$importerItems		= $items_model->getItems();
			$importerItemsTotal	= $items_model->getTotal();
		}

		$tempItems				= array();
		$importerInvalidItems	= array();
		$importervalidatedItems = array();

		foreach ($importerItems as $id => $item)
		{
			$tempItems[$id] = json_decode($item->data);
			$tempItems[$id]->tempId = $item->id;
			$importerInvalidItems[] = $item->invalid_columns;
			$importervalidatedItems[] = $item->validated;
		}

		$finReturn = array(	'items' => $tempItems,
							'count' => $importerItemsTotal,
							'invalid' => $importerInvalidItems,
							'validated' => $importervalidatedItems
						);

		$this->plugin->setResponse($finReturn);
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
		$tempKeys	= array();
		$jinput		= JFactory::getApplication()->input;
		$records	= (array) json_decode($jinput->get('records', '', 'STRING'));
		$batch		= (array) json_decode($jinput->get('batchDetails', '', 'STRING'));

		$invalidDataStr			= $jinput->get('invalidData', '', 'STRING');
		$importedRecStatus		= $jinput->get('imported', false, 'BOOLEAN');

		if (trim($invalidDataStr, '"'))
		{
			$invalidData = (array) json_decode($invalidDataStr);

			foreach ($invalidData as $invalidRecId => $invalidRecField)
			{
				$JForm = array();
				$JForm['id'] = $invalidRecId;

				if ($invalidRecField)
				{
					$JForm['invalid_columns'] = json_encode((object) $invalidRecField);
				}
				else
				{
					$JForm['invalid_columns'] = '';
				}

				$JForm['validated'] = 1;

				$tempId				= $this->saveTemp($JForm);
			}

			$this->plugin->setResponse(true);
		}

		foreach ($records as $index => $record)
		{
			$record = (array) $record;
			$JForm = array();

			if (!empty(array_filter($record)))
			{
				if (isset($record['tempId']))
				{
					$JForm['id'] = $record['tempId'];
				}

				if (isset($record['recordid']))
				{
					$JForm['content_id']	= $record['recordid'];
				}

				if ($importedRecStatus === true)
				{
					$JForm['imported']	= 1;
					$JForm['validated']	= 1;
				}
				else
				{
					$JForm['imported']	= 0;
				}

				$JForm['batch_id']	= $batch['id'];
				$JForm['data']		= json_encode($record);
				$JForm['validated'] = 0;

				$tempId				= $this->saveTemp($JForm);
				$tempKeys[$index]	= $tempId;
			}
		}

		$this->plugin->setResponse($tempKeys);
	}

	/**
	 * saveTemp function to save item data in importer_records table
	 *
	 * @param   Array  $JForm  JFORM data
	 * 
	 * @return  JSON  success of failur status.
	 *
	 * @since  3.0
	 **/
	public function saveTemp($JForm)
	{
		$item_model		= JModelLegacy::getInstance('item', 'ImporterModel');
		$item_model->save($JForm);

		return $item_model->getState("item.id");
	}
}
