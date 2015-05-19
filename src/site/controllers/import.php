<?php
/**
 * @package     Joomla.osian
 * @subpackage  com_osian
 *
 * @copyright   Copyright (C) 2013 - 2014 TWS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';

/**
 * controller class for bulkeditall
 *
 * @package     Joomla.Osian
 * @subpackage  com_osian
 * @since       2.5
 */
class OsianControllerimport extends JController
{
	/**
	 * Function constructor.
	 * 
	 * @since   1.0.0
	 */
	public function __construct()
	{
		parent::__construct();

		// Set the language in the class
		$config		   = JFactory::getConfig();
		$this->app	   = JFactory::getApplication();
		$this->dbo	   = JFactory::getDBO();
		$this->zapp	   = App::getInstance('zoo');
		$this->session = JFactory::getSession();
		$this->params  = $this->app->getParams('com_osian');
		$this->limit   = $this->params->get('limit_id');
		$this->model   = $this->getModel('import');
		$this->jinput  = JFactory::getApplication()->input;
	}

	/**
	 * Function saveBasicDetails stores batch information like batch name, csv filename in the #__batch_items table
	 *
	 * @return  nothing
	 *
	 * @since   1.0.0
	 */
	public function saveBasicDetails()
	{
		$postData = $this->jinput->post;
		$adapter = $postData->get('adapter', '1', 'STRING');
		unset($batch_id);
		$batch_id = $this->model->storeBatch($postData, $adapter);
		$columns = $this->model->getColumns($postData->get('category', '1', 'STRING'), $adapter);
		$this->session->set('columns', '');
		$this->session->set('columns', $columns);
		$this->session->set('batch_id', '');
		$this->session->set('batch_id', $batch_id);
		$this->app->redirect('index.php?option=com_osian&view=import&layout=pastedata&adapter=Osian&sel=bulkimport');
	}

	/**
	 * Function storeCSVData stores data row by row in the #__import_temp table
	 *
	 * @return  nothing
	 *
	 * @since   1.0.0
	 */
	public function storeCSVData()
	{
		$postData = $this->jinput->post;
		$batch_id = $this->session->get('batch_id');
		$csvdata = json_decode($postData->get('csvdata', '1', 'STRING'));
		$start_val = $this->jinput->get('start_val');
		$end_val = $this->jinput->get('end_val');
		$returnArray = $this->model->storeDatatoTemp($csvdata, $start_val, $end_val, $batch_id);
		echo json_encode($returnArray);
		jexit();
	}

	/**
	 * Function validateData used to validate alias, category, related items.
	 *
	 * @return  nothing
	 *
	 * @since   1.0.0
	 */
	public function validateData()
	{
		$postData = $this->jinput->post;
		$batch_id = $this->session->get('batch_id');
		$start_limit = $this->jinput->get('start_limit');
		$end_limit = $this->jinput->get('end_limit');
		$returnArray = $this->model->validateValues($batch_id, $start_limit, $end_limit);
		echo json_encode($returnArray);
		jexit();
	}

	/**
	 * Function getPreviewData used to get records to show preview before import
	 *
	 * @return  nothing
	 *
	 * @since   1.0.0
	 */
	public function getPreviewData()
	{
		$postData = $this->jinput->post;
		$batch_id = $this->session->get('batch_id');
		$csvdata = json_decode($postData->get('csvdata', '1', 'STRING'));
		$start_val = $this->jinput->get('start_val');
		$end_val = $this->jinput->get('end_val');
		$returnArray = $this->model->showPreviewData($start_val, $end_val, $batch_id);
		echo json_encode($returnArray);
		jexit();
	}

	/**
	 * Function importData used to import data to database.
	 *
	 * @return  nothing
	 *
	 * @since   1.0.0
	 */
	public function importData()
	{
		$postData = $this->jinput->post;
		$batch_id = $this->session->get('batch_id');
		$start_val = $this->jinput->get('start_limit');
		$end_val = $this->jinput->get('end_limit');
		$returnArray = $this->model->importData($start_val, $end_val, $batch_id);
		echo json_encode($returnArray);
		jexit();
	}
}
