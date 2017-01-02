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
 * Clienttypes Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_ZooApiResourceClientrecords extends ApiResource
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
		$this->ids		= array_filter(explode("\n", $ids));

		// Get ZOO App instance
		$this->zapp	= App::getInstance('zoo');
		$types	= array();

		// Get instance of blog apps
		$records = $this->zapp->table->item->getByIds($this->ids, $published = false, $user = null, $orderby = '', $ignore_order_priority = false);

		$recordsData = array_map(array($this, 'recordSanitize'), $records);
		$i = 0;

		foreach ($recordsData as $recordId => $recordEle)
		{
			$finalRecords[$i]['recordid'] = $recordId;

			foreach ($recordEle as $recEleId => $recEleVal)
			{
				$finalRecords[$i][$recEleId] = addslashes(strip_tags($recEleVal));
			}

			$i++;
		}

		$this->plugin->setResponse($finalRecords);
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
		$catId = $value->params->get('config.primary_category');
		$catDet	= $this->zapp->table->category->get($catId);

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

		$records_array['name'][0]['value'] = $value->name;
		$records_array['alias'][0]['value'] = $value->alias;
		$recordFinalArray = array();
		$validKeysArray = array('file', 'value');

		foreach ($records_array as $fieldKey => $fieldValue)
		{
			$valueString = '';

			foreach ($fieldValue as $k => $fieldVal)
			{
				if (is_array($fieldVal))
				{
					$keyy	= array_keys($fieldVal);
					$keyyy	= $keyy[0];

					$valueString .= $fieldVal[$keyyy] . "|";
				}
				elseif (is_int($k) || in_array($k, $validKeysArray))
				{
					$valueString .= $fieldVal . "|";
				}
			}

			$recordFinalArray[$fieldKey] = trim($valueString, "|");
		}

		$recordFinalArray['category'] = $catDet->name;

		return $recordFinalArray;
	}
}
