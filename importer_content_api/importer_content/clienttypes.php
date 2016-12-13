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

JLoader::import('components.com_categories.models.categories', JPATH_ADMINISTRATOR);

/**
 * Clienttypes Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_ContentApiResourceClienttypes extends ApiResource
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
		$categories_model 	= JModelLegacy::getInstance('categories', 'CategoriesModel');
		$categories			= $categories_model->getItems();

		$returnCat	= array();

		foreach ($categories as $category)
		{
			$returnCat['All Types'][$category->id] = $category->title;
		}

		print_r($returnCat);

		$this->plugin->setResponse($returnCat);
		die("In get function of importer_content");
	}

	/**
	 * POST function unnecessary
	 *
	 * @return  STRING error message
	 * 
	 * @since  3.0
	 **/
	public function post()
	{
		// $this->plugin->setResponse("POST method is not supporter, try GET method");
		die("in post funtion");
	}
}
