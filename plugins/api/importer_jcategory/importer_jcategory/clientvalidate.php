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
class Importer_JCategoryApiResourceClientvalidate extends ApiResource
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
		// $this->plugin->setResponse("POST method is not supporter, try GET method");
		die("in get funtion");
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
		$columns_array	= $decodeFile = array();
		$jinput			= JFactory::getApplication()->input;

		$checkRecords 	= (array) json_decode($jinput->get('records', '', 'STRING'));
		$batch 			= json_decode($jinput->get('batchDetails', '', 'STRING'));

		foreach ($checkRecords as $record)
		{
			$invalidEle	= $this->validate((array) $record);
			$invalidRec[$record->tempId] = $invalidEle;
		}

		$this->plugin->setResponse($invalidRec);
	}

	/**
	 * POST function unnecessary
	 *
	 * @param   Array  $record          A single record from temp table
	 * @param   Array  $decodeElements  Field element details
	 *
	 * @return  STRING  error message
	 *
	 * @since  3.0
	 **/
	public function validate($record, $decodeElements)
	{
		$invalidFields = null;

		return $invalidFields;
	}
}
