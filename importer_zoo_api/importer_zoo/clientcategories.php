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
// load ZOO config
require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');

/**
 * Clienttypes Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_ZooApiResourceClientcategories extends ApiResource
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
		// Get ZOO App instance
		$zoo	= App::getInstance('zoo');
		$types	= array();

		// Get instance of blog apps
		$blogApp = $zoo->table->application->get(1);

		// Get ty
		foreach($blogApp->getTypes() as $name=>$type)
		{
			$types[$type->id] = $type->name;
		}

		$this->plugin->setResponse($types);
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
}
