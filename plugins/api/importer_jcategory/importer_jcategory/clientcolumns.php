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

JLoader::import('components.com_categories.models.category', JPATH_ADMINISTRATOR);
/**
 * Clientcolumns Resource for Importer_jcategory Plugin.
 *
 * @since  2.5
 */
class Importer_JCategoryApiResourceClientcolumns extends ApiResource
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
		$xmlfile = JPATH_ADMINISTRATOR . "/components/com_categories/models/forms/category.xml";
		$xml = simplexml_load_file($xmlfile);
//~ echo"<pre>";print_r($xml);die;
		$jinput				= JFactory::getApplication()->input;
		$selectedFields 	= $jinput->get('fields', array(), 'ARRAY');

		$selectedFields		= array_filter($selectedFields);

		foreach ($xml->field as $key => $field)
		{
			$object		= new stdClass;
			$fieldInfo	= (array) $field;
			$fieldName	= $fieldInfo['@attributes']['name'];
			$columsescepe=array("hits","asset_id","lft","rgt","level","path","version_note","note","buttonspacer","checked_out","checked_out_time","created_user_id","created_time","modified_user_id","modified_time","rules");

			if($fieldInfo['@attributes']['readonly'] === true  || in_array($fieldInfo['@attributes']['name'],$columsescepe))
			{
				continue;
			}

			if (!empty($selectedFields) && !in_array($fieldName, $selectedFields))
			{
				continue;
			}

			if ($fieldName == 'id')
			{
				$object->readOnly = true;
				$object->primary = 1;
			}
			else
			{
				$object->readOnly = false;
				$object->primary = 0;
			}
			$object->id = $fieldName;
			$object->name = $fieldName;
			$object->type = "text";
			$object->defaultCol = true;
			$object->option = array();
			$colPropertiesArray[] = $object;
		}

		$this->plugin->setResponse($colPropertiesArray);
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
	 * @return  Object  $object  Object of col.
	 *
	 * @since  3.0
	 **/
	public function colProperties($value)
	{
		$object = new stdClass;
		$object->data = $value;

		if (in_array($value, $this->colReadOnly_keys))
		{
			$object->readOnly = "true";
		}

		return $object;
	}
}
