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

$user			= JFactory::getUser();
$userAuthorised	= $user->authorise('core.admin', '');

if($userAuthorised)
{
	// Execute the task.
	$controller = JControllerLegacy::getInstance('Importer');
	$controller->execute(JFactory::getApplication()->input->getCmd('task', 'display', 'STRING'));
	$controller->redirect();
}
elseif(!$user->guest)
{
	echo "<div>You are not authorised to view this page</div>";
}
else
{
	$uri = JUri::getInstance();
	$redirectUrl = urlencode(base64_encode($uri->toString()));
	$redirectUrl = '&return=' . $redirectUrl;

	$joomlaLoginUrl = 'index.php?option=com_users&view=login';
	$finalUrl = $joomlaLoginUrl . $redirectUrl;

	JFactory::getApplication()->enqueueMessage('Please login to open Bulk Tools');
	JFactory::getApplication()->redirect(JRoute::_($finalUrl));
}
