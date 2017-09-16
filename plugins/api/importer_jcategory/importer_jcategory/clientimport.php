<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Importer_zoo
 *
 * @copyright   Copyright (C) 2016 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

JLoader::import('components.com_categories.models.category', JPATH_ADMINISTRATOR);

/**
 * Clientcolumns Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_JCategoryApiResourceClientimport extends ApiResource
{
	/**
	 * GET function to fetch columns in zoo
	 *
	 * @return  JSON  types details
	 *
	 * @since  3.0
	 **/
	public function get()
	{
		die("inside get");
	}

	/**
	 * POST function unnecessary
	 *
	 * @return  STRING  error message
	 *
	 * @since  3.0
	 **/
	public function post()
	{
		// Get the required data
		$jinput		= JFactory::getApplication()->input;
		$records	= $jinput->get('records', '', 'STRING');
		$batch		= $jinput->get('batchDetails', '', 'STRING');
		$this->records	= json_decode($records);
		$this->batch	= json_decode($batch);
		$newIds			= array();

		// Include the table
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_categories/tables');

		// Create the response object
		$response = new stdClass();
		$response->records = array();
		$response->invalid = new stdClass();

		foreach ($this->records as $record)
		{
			// Get the model
			$this->category_model 	= JModelLegacy::getInstance('Category', 'CategoriesModel');

			if (empty(array_filter((array) $record)))
			{
				continue;
			}

			// Get the tempId & unset it
			$tempId = $record->tempId;
			unset($record->tempId);

			// Save the record in the table
			$newId = $this->save($record);

			if ($newId)
			{
				$record->id = $newId;
				$record->tempId = $tempId;
				$response->records[] = $record;
				$response->invalid->$tempId = null;
			}
			else
			{
				$response->invalid->$tempId = 1;
			}
		}

		$this->plugin->setResponse($response);
	}

	/**
	 * POST function unnecessary
	 *
	 * @param   Object  $record  Single record data
	 *
	 * @return  STRING  error message
	 *
	 * @since  3.0
	 **/
	public function save($record)
	{
		// Convert the object to an array
		$record = (array) $record;

		// Actually save data
		if ($this->category_model->save($record))
		{
			// Return the category id
			return $this->category_model->getState("category.id");
		}

		return false;
	}
}
