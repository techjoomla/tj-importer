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

// Load ZOO config
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';

/**
 * Clientcolumns Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_ZooApiResourceClientimport extends ApiResource
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
		$jinput		= JFactory::getApplication()->input;
		$records	= $jinput->get('records', '', 'STRING');
		$batch		= $jinput->get('batchDetails', '', 'STRING');

		$this->records	= json_decode($records);
		$this->batch	= json_decode($batch);
		$newIds			= array();

		// Get ZOO App instance
		$this->zapp	= App::getInstance('zoo');

		foreach ($this->records as $record)
		{
			if (empty(array_filter((array) $record)))
			{
				continue;
			}

			if ($record->recordid)
			{
				// Edit function call
				$this->updateRec($record);
			}
			else
			{
				// Add new function call
				$newId = $this->saveNew($record);
				$record->recordid = $newId;
			}

			$newIds[] = $record;
		}

		$this->plugin->setResponse($newIds);
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
	public function saveNew($record)
	{
		$newItem 	= $this->zapp->object->create('Item');

		// Set blog as zoo application
		$newItem->application_id = 1;

		// Set item type from batch details
		$newItem->type = $this->batch->params->type;

		// Set item name and alias
		$newItem->name	= $record->name;
		$newItem->alias	= $record->alias;

		// By default publish state
		$newItem->state	= 1;

		$newItem->created = $this->zapp->date->create()->toSQL();
		$newItem->publish_up = $this->zapp->date->create()->toSQL();

		$this->zapp->table->item->save($newItem);

		return $newItem->id;
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
	public function updateRec($record)
	{
		$item	= $this->zapp->table->item->get($record->recordid);

		$item->name		= $record->name;
		$item->alias	= $record->alias;

		$this->zapp->table->item->save($item);

		return $item->id;
	}
}
