<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Importer
 *
 * @copyright   Copyright (C) 2016 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

/**
 * Importer Plugin.
 *
 * @since  3.0
 */
class PlgAPIImporter extends ApiPlugin
{
	/**
	 * Constructor
	 *
	 * @param   string  &$subject  The name of the Plugin group.
	 * @param   array   $config    Config params array.
	 *
	 *
	 * @since  3.0
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject = 'api', $config = array());

		ApiResource::addIncludePath(dirname(__FILE__) . '/importer');

		// Set the login resource to be public
		$this->setResourceAccess('item', 'public', 'get');
		$this->setResourceAccess('item', 'public', 'post');
		$this->setResourceAccess('batch', 'public', 'get');
		$this->setResourceAccess('batch', 'public', 'post');
	}
}
