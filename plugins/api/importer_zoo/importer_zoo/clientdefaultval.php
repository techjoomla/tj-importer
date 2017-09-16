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
 * Clientcolumns Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_ZooApiResourceClientdefaultval extends ApiResource
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
		$jinput			= JFactory::getApplication()->input;
		$selectedType	= $jinput->get('type', '', 'STRING');

		$app		= JFactory::getApplication();
		$cparams	= $app->getParams('com_osian');
		$mapping	= $cparams->get('class_names');
		$mappingArr	= explode("\n", $mapping);
		$selectedCatId = '';

		foreach ($mappingArr as $mapVal)
		{
			$selectedCatId	= '';
			$val			= explode("=", $mapVal);

			if ($selectedType === trim($val[1]))
			{
				$selectedCatId	= trim($val[0]);
				break;
			}
		}

		$this->helper	= new ZooApiHelper;

		// Get ZOO App instance
		$this->zapp	= App::getInstance('zoo');

		$allCategories = $this->zapp->table->category->getAll(1, '', true);

		$finalArray = array();

		foreach ($allCategories as $t => $uy)
		{
			if ($uy->name == 'search')
			{
				continue;
			}

			if ($uy->id == $selectedCatId || $uy->parent == $selectedCatId || $selectedCatId == '')
			{
				// $catCount		= $this->zapp->table->item->getItemCountFromCategory(1, $uy->id);
				$catCount			= count($uy->item_ids);
				$parentData			= $uy->parent ? $this->zapp->table->category->getById($uy->parent) : '';

				$catFormat			= new stdClass;
				$catFormat->id		= $uy->id;
				$catFormat->name	= $uy->parent ? $parentData[0]->name . "." . $uy->name . " (Id : " . $uy->id . ")" : $uy->name . " (Id : " . $uy->id . ")";

				$finalArray[$catFormat->name] = '"' . $uy->id . '"';
			}
		}

		ksort($finalArray);
		$categoryListArray = array_flip($finalArray);

		foreach ($categoryListArray as $id => $val)
		{
			$categoryListArray [$id] = preg_match('/\([0-9]+\)/', $val) ? "--- " . $val : "- " . $val;
		}

		$returnArray = array();

		if (count($categoryListArray) > 2)
		{
			array_shift($categoryListArray);
		}

		$singleFields			= new stdClass;
		$singleFields->type		= 'select';
		$singleFields->label	= 'Category';
		$singleFields->id		= 'category';
		$singleFields->options	= $categoryListArray;

		$returnArray[] = $singleFields;

		$this->plugin->setResponse($returnArray);
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
		die('Post method not available');
	}
}
