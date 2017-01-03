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
jimport('joomla.form.form');

JLoader::import('components.com_tmt.models.question', JPATH_ADMINISTRATOR);
/**
 * Clientcolumns Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_LmsApiResourceClientcolumns extends ApiResource
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
		$xmlfile = JPATH_ADMINISTRATOR . "/components/com_tmt/models/forms/question.xml";
		$xml = simplexml_load_file($xmlfile);

		$jinput				= JFactory::getApplication()->input;
		$selectedFields 	= $jinput->get('fields', array(), 'ARRAY');

		$selectedFields		= array_filter($selectedFields);

		foreach ($xml->fieldset->field as $key => $field)
		{
			$rtrObk		= new stdClass;
			$fieldInfo	= (array) $field;
			$fieldName	= $fieldInfo['@attributes']['name'];

			if (!empty($selectedFields) && !in_array($fieldName, $selectedFields))
			{
				continue;
			}

			$rtrObk->data				= $fieldName;
			$columns_array[$fieldName]	= ucwords($fieldName);
			$columnsId_array[]			= $fieldName;
			$columnsName_array[]		= ucwords($fieldName);
			$colProperties_array[]		= $rtrObk;
		}

		$finalReturn = array(
						'colProperties' => $colProperties_array,
						'colFields' => $columns_array,
						'colIds' => $columnsId_array,
						'colName' => $columnsName_array
						);

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
