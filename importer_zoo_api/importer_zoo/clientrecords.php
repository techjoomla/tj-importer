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
require_once JPATH_SITE . '/plugins/api/importer_zoo/helper.php';

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
	}

	/**
	 * POST function to fetch records based on the provided id's in zoo
	 * 
	 * @return  JSON  records details
	 * 
	 * @since  3.0
	 **/
	public function post()
	{
		$jinput			= JFactory::getApplication()->input;

		$this->type		= $jinput->get('type', '', 'STRING');
		$fields			= $jinput->get('fields', '', 'ARRAY');
		$ids			= $jinput->get('ids', '', 'STRING');
		$this->ids		= array_filter(explode(",", $ids));
		$this->helper	= new ZooApiHelper;

		$this->fields	= array();
		$this->primaryKey = '';

		foreach ($fields as $field)
		{
			$this->fields[][$field['id']] = $field['name'];

			if ($field['primary'])
			{
				$this->primaryKey = $field['id'];
			}
		}

		// Get ZOO App instance
		$this->zapp	= App::getInstance('zoo');
		$types	= array();

		$filePath	= JPATH_SITE . '/media/zoo/applications/blog/types/' . $this->type . '.config';

		if (JFile::exists($filePath))
		{
			$decodeFile		= (array) json_decode(JFile::read($filePath));
			$this->decodeElements = (array) $decodeFile['elements'];
		}
		else
		{
			$this->plugin->setResponse('invalid type');
		}

		// Get instance of blog apps
		// $records = $this->zapp->table->item->getByIds($this->ids);
		$records = $this->helper->getByAliases($this->ids, $this->type);

		$recordsData = array_map(array($this, 'recordSanitize'), $records);
		$i = 0;

		foreach ($recordsData as $recordId => $recordEle)
		{
			$finalRecords[$i][$this->primaryKey] = $recordId;

			foreach ($recordEle as $recEleId => $recEleVal)
			{
				// $finalRecords[$i][$recEleId] = trim(json_encode($recEleVal, JSON_UNESCAPED_UNICODE), '"');
				$finalRecords[$i][$recEleId] = $recEleVal;
			}

			$i++;
		}

		$this->plugin->setResponse($finalRecords);
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
		$catId		= $value->params->get('config.primary_category');

		// $catDet		= $this->zapp->table->category->get($catId);
		// $catParent	= ($catDet->parent) ? $this->zapp->table->category->get($catDet->parent)->name : '';

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

			switch ($this->decodeElements[$fieldKey]->type)
			{
				case 'radio':
						$optVal = (array) $records_array[$fieldKey]['option'];
						$valueString = implode('|', $optVal);
					break;
				case 'select':
						$optVal = (array) $records_array[$fieldKey]['option'];
						$valueString = implode('|', $optVal);
					break;
				case 'relateditemspro':

						// Call function to fetch id's and then alias of the related records
						$riProData = $this->getRIprolist($value->id, $fieldKey);
						$valueString = $riProData['alias'];
					break;
				case 'imagepro':
						foreach ($fieldValue as $fv)
						{
							$valueString .= $fv['file'] . "|";
						}
					break;
				case 'date':
						foreach ($fieldValue as $k => $fieldVal)
						{
							try
							{
								$valueString = JHtml::date($fieldVal['value'], 'Y-m-d H:i:s') . "|";
							}
							catch (Exception $e)
							{
								$valueString = $fieldVal['value'] . "|";
							}
						}
					break;
				default :
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
					break;
			}

			$recordFinalArray[$fieldKey] = trim($valueString, "|");
		}

		// $recordFinalArray['category'] = $catParent ? $catParent . "/" . $catDet->name : $catDet->name;
		$recordFinalArray['category'] = $catId;
		$recordFinalArray['state'] = $value->state;

		return $recordFinalArray;
	}

	/**
	 * Function if ripro field selected, return pipe separated values
	 *
	 * @param   int  $itemid      itemid
	 *
	 * @param   int  $element_id  element id
	 *
	 * @return  array  $element_value  ripro field id to buildItemData() function
	 */
	public function getRIprolist($itemid, $element_id)
	{
		$this->dbo	   = JFactory::getDBO();

		$query_field = $this->dbo->getQuery(true);
		$query_field->select('DISTINCT ritem_id')
					->from('#__zoo_relateditemsproxref')
					->where('item_id = ' . $itemid . ' AND element_id = ' . $this->dbo->quote($element_id));
		$this->dbo->setQuery($query_field);
		$ritems   = $this->dbo->loadObjectList();
		$mast_arr = array();

		if (!empty($ritems))
		{
			$i = 0;

			foreach ($ritems as $ri)
			{
				$item			 = $this->zapp->table->item->get($ri->ritem_id);
				$mast_arr_alias[] = $item->alias;
				$mast_arr_names[] = $item->name;

				$i++;
			}

			$mast_arr_alias   = array_filter($mast_arr_alias, 'strlen');
			$mast_items_alias = implode("|", $mast_arr_alias);
			$mast_arr_names   = array_filter($mast_arr_names, 'strlen');
			$mast_items_names = implode("|", $mast_arr_names);
			$element_value['name']  = $mast_items_names;
			$element_value['alias'] = $mast_items_alias;
		}
		else
		{
			$element_value = '';
		}

		return $element_value;
	}
}
