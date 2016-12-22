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
			$decodeElements = (array)$decodeFile['elements'];


			if(!empty($fields))
			{
				$filppedFields 	= array_flip($fields);
				$decodeElements = array_intersect_key($decodeElements, $filppedFields);
			}

			// Core fields of zoo
			$colBasicEle_array['recordid']	= 'recordid';
			$colBasicEle_array['name']		= 'name';
			$colBasicEle_array['alias']		= 'alias';

			// Non-core fields of zoo
			$colElement_array = array_map(array($this, 'sanitize'), $decodeElements);

			// Merging core and non-core fields id=>name pair
			// selected field filter needs to apply on columns_array
			$columns_array = array_merge($colBasicEle_array, $colElement_array);

			if(!empty($fields))
			{
				$filppedFields 	= array_flip($fields);
				$columns_array = array_intersect_key($columns_array, $filppedFields);
			}

			// Getting only id's of columns_array
			$columnsId_array 			= array_keys($columns_array);

			// Getting Id's of preprogram fields to make them readOnly
			$columnsIdFIlters_array 	= array_map(array($this, 'setColumns'), $decodeElements);

			// Getting read-only keys
			$colReadOnly_keys			= array_keys(array_filter($columnsIdFIlters_array));
			array_push($colReadOnly_keys, "recordid");
			$this->colReadOnly_keys			= $colReadOnly_keys;

			$colProperties_array 	= array_map(array($this, 'colProperties'), $columnsId_array);

			$columnsName_array 			= array_values($columns_array);
			$colReadOnly_array 			= ["recordid", "name"];
			
			$finalReturn = [colProperties=>$colProperties_array, colFields=>$columns_array, colIds=>$columnsId_array, colName=>$columnsName_array];

		}
		else
		{
			die("Type file not found");
		}

		$this->plugin->setResponse($finalReturn);
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
	
	public function sanitize($value)
    {
        return strip_tags(trim($value->name));
    }

	public function setColumns($value)
    {
		$retrnVal = ($value->type == 'preprogram') ? "readOnly" : "";
		return $retrnVal;
    }

	public function colProperties($value)
    {
		$rtrObk = new stdClass;
		$rtrObk->data = $value;

		if(in_array($value, $this->colReadOnly_keys))
			$rtrObk->readOnly = "true";
		
		return $rtrObk;
    }
}
