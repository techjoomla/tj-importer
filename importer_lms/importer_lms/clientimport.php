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

JLoader::import('components.com_tmt.models.question', JPATH_ADMINISTRATOR);

/**
 * Clientcolumns Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_LmsApiResourceClientimport extends ApiResource
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

		$this->questions_model 	= JModelLegacy::getInstance('question', 'TmtModel');
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tmt/tables');

		foreach ($this->records as $record)
		{
			if (empty(array_filter((array) $record)))
			{
				continue;
			}

			$tempId = '';
			$tempId = $record->tempId;
			$recordidd = $record->recordid;

			if ($record->recordid)
			{
				unset($record->recordid);
				unset($record->tempId);

				$newId = $this->save($record);
				$record->recordid = $newId;
				$record->id = $newId;
			}
			else
			{
				// Add new function call
				unset($record->tempId);
				$newId = $this->save($record);
				$record->recordid = $newId;
				$record->id = $newId;
			}

			$record->tempId = $tempId;

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
	public function save($record)
	{
		$record = (array) $record;

		$record['answers_text'] = array('demo answer 1', 'demo answer 2');
		$record['answers_iscorrect_hidden'] = array(0,1);
		$record['answers_marks'] = array(0,10);
		$record['answer_id_hidden'] = array(0,0);

		$itemId = $this->questions_model->save($record);

		return $itemId;
	}
}
