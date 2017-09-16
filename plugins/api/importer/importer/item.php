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
		$callStatus		= $jinput->get('getStatus', 0, 'INT');

		$limit			= $jinput->get('limit', 20, 'INT');
		$offset			= $jinput->get('offset', 0, 'INT');

		if ( ($batch_id > 0) && ($offset >= 0) && $limit > 0 && !$callStatus)
		{
			$items_model->setState('filter.batch_id', $batch_id);
			$items_model->setState('filter.limit', $limit);
			$items_model->setState('filter.offset', $offset);

			$importerItems	= $items_model->getItems();
			$importerItemsTotal	= $items_model->getTotal();

			$tempItems				= array();
			$importerInvalidItems	= array();
			$importervalidatedItems = array();

			foreach ($importerItems as $id => $item)
			{
				$tempItems[$id]				= json_decode($item->data);
				$tempItems[$id]->tempId		= $item->id;
				$importerInvalidItems[]		= $item->invalid_columns;
				$importervalidatedItems[]	= $item->validated;
			}

			$finReturn = array(	'items' => $tempItems,
								'count' => $importerItemsTotal,
								'invalid' => $importerInvalidItems,
								'validated' => $importervalidatedItems
							);

			$this->plugin->setResponse($finReturn);
		}
		else
		{
			$items_model->setState('filter.batch_id', $batch_id);

			// $importerItems		= $items_model->getItems();
			$importerItemsTotal	= $items_model->getTotal();

			$itemsModelValidated 	= JModelLegacy::getInstance('items', 'ImporterModel');
			$itemsModelValidated->setState('filter.batch_id', $batch_id);
			$itemsModelValidated->setState('filter.validated', 1);
			$importerItemsValidatedTotal = $itemsModelValidated->getTotal();

			$itemsModelImported 	= JModelLegacy::getInstance('items', 'ImporterModel');
			$itemsModelImported->setState('filter.batch_id', $batch_id);
			$itemsModelImported->setState('filter.imported', 1);
			$importerItemsImportedTotal = $itemsModelImported->getTotal();

			$itemsModelInvalid 	= JModelLegacy::getInstance('items', 'ImporterModel');
			$itemsModelInvalid->setState('filter.batch_id', $batch_id);
			$itemsModelInvalid->setState('filter.invalid_columns', 1);
			$importerItemsInvalidTotal = $itemsModelInvalid->getTotal();

			$statusRtr = array(
								'itemsTotal' => $importerItemsTotal,
								'validatedTotal' => $importerItemsValidatedTotal,
								'importedTotal' => $importerItemsImportedTotal,
								'invalidTotal' => $importerItemsInvalidTotal
								);

			$this->plugin->setResponse($statusRtr);
		}
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
		$records	= (array) json_decode($jinput->get('records', '', 'RAW'));
		$batch		= (array) json_decode($jinput->get('batchDetails', '', 'STRING'));
		$db			= JFactory::getDbo();

		$invalidDataStr			= $jinput->get('invalidData', '', 'STRING');
		$importedRecStatus		= $jinput->get('imported', false, 'BOOLEAN');
		$primaryKey				= $jinput->get('primaryKey', '', 'STRING');

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
			$testRecord		= (array) $record;
			$removeRecordId = $testRecord['tempId'];

			unset($testRecord['tempId']);

			// Condition to remove empty records from temp table
			if (empty(array_filter($testRecord)) && $removeRecordId)
			{
				$query = $db->getQuery(true);

				// Delete empty records.
				$conditions = array(
					$db->quoteName('id') . ' = ' . $removeRecordId
				);

				$query->delete($db->quoteName('#__importer_items'));
				$query->where($conditions);

				$db->setQuery($query);

				$result = $db->execute();
				continue;
			}

			$record = (array) $record;
			$JForm = array();

			// Condition to update or add non-empty records to temp table
			if (!empty(array_filter($record)))
			{
				if (isset($record['tempId']))
				{
					$JForm['id'] = $record['tempId'];
				}

				if (isset($record[$primaryKey]))
				{
					$JForm['content_id']	= $record[$primaryKey];
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

				// Condition to update Id and tempId in data column
				if ($tempId)
				{
					$record['tempId']	= $tempId;
					$JForm['data']		= json_encode($record);
					$JForm['id']		= $tempId;

					$this->saveTemp($JForm);
				}

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
		$this->item_model		= JModelLegacy::getInstance('item', 'ImporterModel');
		$this->item_model->save($JForm);

		return $this->item_model->getState("item.id");
	}
}
