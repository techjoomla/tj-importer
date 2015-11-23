<?php
/**
 * @version    SVN: <svn_id>
 * @package    TMT
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (C) 2012-2013 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

	// Require the base controller

	require_once JPATH_COMPONENT . '/controller.php';

	// Require specific controller if requested
	if ($controller = JRequest::getWord('controller'))
	{
		$path = JPATH_COMPONENT . '/controllers/' . $controller . '.php';

		if (file_exists($path))
		{
			require_once $path;
		}
		else
		{
			$controller = '';
		}
	}

// Create the controller
$classname	= 'ImporterController' . $controller;
$controller   = new $classname;

// Perform the Request task
$controller->execute(JRequest::getWord('task'));

// Redirect if set by the controller
$controller->redirect();
