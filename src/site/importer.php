<?php
/**
 * @version    SVN: <svn_id>
 * @package    TMT
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (C) 2012-2013 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Execute the task.
$controller = JControllerLegacy::getInstance('Importer');
$controller->execute(JFactory::getApplication()->input->getCmd('task', 'display', 'STRING'));
$controller->redirect();
