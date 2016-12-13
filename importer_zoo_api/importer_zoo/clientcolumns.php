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

		$type 	= $jinput->get('type', '', 'STRING');
		$types	= explode("_", $type);

		$filePath	= JPATH_SITE . '/media/zoo/applications/blog/types/' . $types[0] . '.config';

		if (JFile::exists($filePath))
		{
			$decodeFile = (array) json_decode(JFile::read($filePath));

			$columns_array['recordid']	= 'recordid';
			$columns_array['name']		= 'name';
			$columns_array['alias']		= 'alias';

			foreach ($decodeFile['elements'] as $k => $ele)
			{
				$columns_array[$k] = $ele->name;
			}
		}
		else
		{
			die("Type file not found");
		}

		$this->plugin->setResponse($columns_array);
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
}
