<?php
/**
	* @version    SVN: <svn_id>
	* @package    Osians
	* @copyright  Copyright (C) 2005 - 2014. All rights reserved.
	* @license    GNU General Public License version 2 or later; see LICENSE.txt
	*/

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

// Execute the task.
$controller	= JController::getInstance('Osian');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
