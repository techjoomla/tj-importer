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

/**
 * Clientcolumns Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_ZooApiResourceClientcolumns extends ApiResource
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
		$columns_array	= $decodeFile = array();
		$jinput			= JFactory::getApplication()->input;

		$type 		= $jinput->get('type', '', 'STRING');
		$fields 	= $jinput->get('fields', array(), 'ARRAY');
		$fields		= array_filter($fields);

		$types	= explode("_", $type);

		$filePath	= JPATH_SITE . '/media/zoo/applications/blog/types/' . $types[0] . '.config';

		if (JFile::exists($filePath))
		{
			$decodeFile		= (array) json_decode(JFile::read($filePath));
			$decodeElements = (array) $decodeFile['elements'];

			if (!empty($fields))
			{
				$filppedFields 	= array_flip($fields);
				$decodeElements = array_intersect_key($decodeElements, $filppedFields);
			}

			// Core fields of zoo
			$colBasicEle_array['zooid']	= 'Zoo-id';
			$colBasicEle_array['name']		= 'Zoo Name';
			$colBasicEle_array['alias']		= 'Alias';
			$colBasicEle_array['state']		= 'State';
			$colBasicEle_array['category']	= 'Category Id';

			// Non-core fields of zoo
			$colElement_array = array_map(array($this, 'sanitize'), $decodeElements);

			// Merging core and non-core fields id=>name pair
			// selected field filter needs to apply on columns_array
			$columns_array = array_merge($colBasicEle_array, $colElement_array);

			// Getting only selected fields from step1.
			if (!empty($fields))
			{
				$filppedFields 	= array_flip($fields);
				$columns_array = array_intersect_key($columns_array, $filppedFields);
			}

			$escapeColumns 	= array(
									"_itemaccess" => 1,
									"_itemauthor" => 1,
									"_itemcategory" => 1,
									"_itemcommentslink" => 1,
									"_itemcreated" => 1,
									"_itemedit" => 1,
									"_itemfrontpage" => 1,
									"_itemhits" => 1,
									"_itemlink" => 1,
									"_itemmodified" => 1,
									"_itemname" => 1,
									"_itemprint" => 1,
									"_itempublish_down" => 1,
									"_itempublish_up" => 1,
									"_itemprevnext" => 1,
									"_itemsearchable" => 1,
									"_itemstate" => 1,
									"_itemtag" => 1,
									"_staticcontent" => 1
									);

			// Escape the unwanted columns
			$columns_array = array_diff_key($columns_array, $escapeColumns);

			// Getting only id's of columns_array
			$columnsId_array 			= array_keys($columns_array);

			// Getting Id's of preprogram fields to make them readOnly
			$columnsIdFIlters_array 	= array_map(array($this, 'setColumns'), $decodeElements);

			// Getting read-only keys
			$colReadOnly_keys			= array_keys(array_filter($columnsIdFIlters_array));
			array_push($colReadOnly_keys, "zooid");
			$this->colReadOnly_keys			= $colReadOnly_keys;

			$colProperties_array			= array_map(array($this, 'colProperties'), $columnsId_array);
			$columnsName_array				= array_values($columns_array);

			$myfinalArrayy = array();
			$defaultColumns = array('zooid', 'name', 'alias');

			foreach ($columns_array as $colK => $colV)
			{
				$tempKetDetails		= $decodeElements[$colK];

				if ($optionArray = (array) $tempKetDetails->option)
				{
					$optionVal = array();

					foreach ($optionArray as $options)
					{
						$optionVal[] = $options->value;
					}
				}

				$format				= new stdClass;
				$format->id			= $colK;
				$format->name		= $colV;
				$format->type		= ($tempKetDetails->type == "relateditemspro" || $tempKetDetails->type == "radio") ? "autocomplete" : "text";
				$format->readOnly	= in_array($colK, $colReadOnly_keys);
				$format->primary	= ($colK == 'zooid' ? 1 : 0);
				$format->defaultCol	= in_array($colK, $defaultColumns);
				$format->option		= $optionVal;

				$myfinalArrayy[]	= $format;
			}

			/*
			$finalReturn['colProperties']	= $colProperties_array;
			$finalReturn['colFields']		= $columns_array;
			$finalReturn['colIds']			= $columnsId_array;
			$finalReturn['colName']			= $columnsName_array;
			*/
		}
		else
		{
			die("Type file not found");
		}

		$this->plugin->setResponse($myfinalArrayy);
	}

	/**
	 * colProperties function to set column properties
	 * like read-only
	 *
	 * @param   Object  $value  field valuevalue data
	 * 
	 * @return  Object  $rtrObk  Object of col.
	 *
	 * @since  3.0
	 **/
	public function correctFormat($value)
	{
		echo "<pre>";
		print_r($value);
		die;
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
		// $this->plugin->setResponse("POST method is not supporter, try GET method");
		die("in post funtion");
	}

	/**
	 * sanitize function to set column properties
	 * like read-only
	 *
	 * @param   Object  $value  field valuevalue data
	 * 
	 * @return  Object  $retrnVal  Object of col.
	 *
	 * @since  3.0
	 **/
	public function sanitize($value)
	{
		return strip_tags(trim($value->name));
	}

	/**
	 * setColumns function to set column properties
	 * like read-only
	 *
	 * @param   Object  $value  field valuevalue data
	 * 
	 * @return  Object  $retrnVal  Object of col.
	 *
	 * @since  3.0
	 **/
	public function setColumns($value)
	{
		$retrnVal = ($value->type == 'preprogram') ? "readOnly" : "";

		return $retrnVal;
	}

	/**
	 * colProperties function to set column properties
	 * like read-only
	 *
	 * @param   Object  $value  field valuevalue data
	 * 
	 * @return  Object  $rtrObk  Object of col.
	 *
	 * @since  3.0
	 **/
	public function colProperties($value)
	{
		$rtrObk = new stdClass;
		$rtrObk->data = $value;

		if (in_array($value, $this->colReadOnly_keys))
		{
			$rtrObk->readOnly = "true";
		}

		return $rtrObk;
	}
}
