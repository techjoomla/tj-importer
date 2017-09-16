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

JLoader::import('components.com_tmt.models.questions', JPATH_ADMINISTRATOR);

/**
 * Clienttypes Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_LmsApiResourceClientrecords extends ApiResource
{
	/**
	 * GET function to fetch different types/categories in zoo
	 *
	 * @return  JSON  types details
	 *
	 * @since  3.0
	 **/
	public function get()
	{
		$jinput			= JFactory::getApplication()->input;

		$this->type		= $jinput->get('type', '', 'STRING');
		$this->fields	= $jinput->get('fields', '', 'ARRAY');
		$ids			= $jinput->get('ids', '', 'STRING');
		$this->ids		= $ids;

		$questions_model 	= JModelLegacy::getInstance('questions', 'TmtModel');

		$questions_model->setState('filter.chekcing', $this->ids);

		$qcheck = $questions_model->getItems();

		foreach ($qcheck as $key => $value)
		{
			switch ($value->type)
			{
				case 'COM_TMT_QTYPE_MCQ_SINGLE' :
					$value->type = 'radio';
					break;
				case 'COM_TMT_QTYPE_MCQ_MULTIPLE' :
					$value->type = 'checkbox';
					break;
			}

			$qcheck[$key]->recordid = $value->id;
		}

		$this->plugin->setResponse($qcheck);

		/** output
		 * $recordsData = array_map(array($this, 'recordSanitize'), $records);
		 * $finalRecords[$i][$recEleId] = addslashes(strip_tags($recEleVal[0]['value'])); 
		**/
	}

	/**
	 * POST function unnecessary
	 * 
	 * @return  JSON  types details
	 * 
	 * @since  3.0
	 **/
	public function post()
	{
		// $this->plugin->setResponse("POST method is not supporter, try GET method");
		die("in post funtion");
	}

	/**
	 * POST function unnecessary
	 * 
	 * @param   Array  $value  JFORM data
	 * 
	 * @return  JSON  types details
	 * 
	 * @since  3.0
	 **/
	public function recordSanitize($value)
	{
		$flippedFields	= array_flip($this->fields);
		$recordEle		= (array) $value->elements;

		if (!empty(array_filter($flippedFields)))
		{
			$records_array = array_intersect_key($recordEle, $flippedFields);
		}
		else
		{
			$records_array = $recordEle;
		}

		return $records_array;
	}
}
