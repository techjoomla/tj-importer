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
class ImporterViewimport extends JViewLegacy
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
		//$model = &$this->getModel();
		$classification  = $this->get('Categories');
		//print_r($classification);die('mukta');
		$this->assignRef('categories', $classification);
		$dynamic_columns  = $this->get('DynamicCols');
		//print_r($classification);die('mukta');
		$this->assignRef('dynamic_columns', $dynamic_columns);
		$user = &JFactory::getUser();
		$this->assignRef('id', $user->id);
		$jinput  = JFactory::getApplication()->input;
		$layout = $jinput->get('layout');
		$imported_val = $jinput->get('imported');
		if($layout == 'validate')
		{
			$invalid_data  = $this->get('InvalidData');
			$this->assignRef('invalid_data', $invalid_data);
			$oinvalid_data  = $this->get('oInvalidData');
			$this->assignRef('oinvalid_data', $oinvalid_data);
		}
		if($layout == 'preview' && $imported_val == 1)
		{
			$previewlink  = $this->get('PreviewLink');
			$this->assignRef('previewlink', $previewlink);
			$imported  = $this->get('ImportedCount');
			$this->assignRef('imported', $imported);
			$not_imported  = $this->get('NImportedCount');
			$this->assignRef('not_imported', $not_imported);
			$total  = $this->get('Total');
			$this->assignRef('total', $total);
		}
		/*$clas_all_data  = $model->getAllClassList();
		$this->assignRef('cls_all_data', $clas_all_data);
		$user = &JFactory::getUser();
		$this->assignRef('id', $user->id);
		$getclsdata  = $model->getClassificationData();
		$this->assignRef('class_data', $getclsdata);*/

		parent::display($tpl);
	}
}
