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

require_once JPATH_SITE . '/components/com_osian/classes/build_hierarchy.php';

/**
 * Clienttypes Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_ZooApiResourceClienttypes extends ApiResource
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
		$obj				 = new Build_Hierarchy;
		$all_classifications = $obj->BuildTree();
		$class_drop_down = $typeList = $mast_drop_down	= array();

		foreach ($all_classifications as $key => $value)
		{
			if ($value['parent'] === 0 && $value['name'] != '')
			{
				switch ($value['ismasterlist'])
				{
					case 0:
						$class_drop_down[$value['config'] . "/" . $value['id'] . "/" . $value['ismasterlist']] = $value['name'];
						break;
					case 1:
						$mast_drop_down[$value['config'] . "/" . $value['id'] . "/" . $value['ismasterlist']] = $value['name'];
						break;
				}
			}
		}

		asort($class_drop_down);
		asort($mast_drop_down);
		$typeList['classifications']	= $class_drop_down;
		$typeList['masterlists']		= $mast_drop_down;

		echo "<pre>";
		print_r($typeList);

		$this->plugin->setResponse($typeList);
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
