<?php
/**
 * @package     Joomla.osian
 * @subpackage  com_osian
 *
 * @copyright   Copyright (C) 2013 - 2014 TWS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * controller class for bulkeditall
 *
 * @package     Joomla.Osian
 * @subpackage  com_osian
 * @since       2.5
 */
class OsianViewimport extends JView
{
	/**
	 * Function display.
	 *
	 * @param   int  $tpl  subject
	 *
	 * @return   nothing
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$model = &$this->getModel();
		$classification  = $model->getCategories();
		$this->assignRef('categories', $classification);
		$user = &JFactory::getUser();
		$this->assignRef('id', $user->id);
		$invalid_data  = $model->getInvalidData();
		$this->assignRef('invalid_data', $invalid_data);
		/*$clas_all_data  = $model->getAllClassList();
		$this->assignRef('cls_all_data', $clas_all_data);
		$user = &JFactory::getUser();
		$this->assignRef('id', $user->id);
		$getclsdata  = $model->getClassificationData();
		$this->assignRef('class_data', $getclsdata);*/

		parent::display($tpl);
	}
}
