<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * HTML View class for the search component
 *
 * @since  1.0
 */
class ImporterViewImporter extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		// Add language constant to use in javascript code.
		JText::script('COM_IMP_TOTAL_BATCHES_FOR');
		JText::script('COM_IMP_NO_BATCHES_FOR');
		JText::script('COM_IMP_FETCHING_TEMP_RECORDS');
		JText::script('COM_IMP_FETCHING_CLT_RECORDS');
		JText::script('COM_IMP_TOT_CSV_REC');
		JText::script('COM_IMP_TOT_TMP_REC');
		JText::script('COM_IMP_TOT_VLD_REC');
		JText::script('COM_IMP_TOT_INVLD_REC');
		JText::script('COM_IMP_TOT_IMP_REC');
		JText::script('COM_IMP_REC_IMPORTED');
		JText::script('COM_IMP_REC_IMPORTING');
		JText::script('COM_IMP_REC_SAVING_TEMP');
		JText::script('COM_IMP_REC_VALIDATING');
		JText::script('COM_IMP_REC_VALIDATED');
		JText::script('COM_IMP_REC_SAVED_TEMP');

		JText::script('COM_IMP_BATCHES_TH_NAME');
		JText::script('COM_IMP_BATCHES_TH_CRE_DATE');
		JText::script('COM_IMP_BATCHES_TH_MOD_DATE');
		JText::script('COM_IMP_BATCHES_TH_CRE_USER');

		JText::script('COM_IMP_ERROR_MSG');

		$jinput  = JFactory::getApplication()->input;
		$user = JFactory::getUser();

		$this->clientApp	= $jinput->get('clientapp', '', 'STRING');
		$this->batchId		= $jinput->get('batch_id', '', 'INT');
		$this->userId		= $user->id;
		$this->userName		= $user->name;

		parent::display();
	}
}
