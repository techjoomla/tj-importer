<?php
/**
 * @package	API
 * @version 1.5
 * @author 	Brian Edgerton
 * @link 	http://www.edgewebworks.com
 * @copyright Copyright (C) 2011 Edge Web Works, LLC. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

JLoader::import('components.com_importer.models.batch', JPATH_SITE);
JLoader::import('components.com_importer.models.batches', JPATH_SITE);
JLoader::import('components.com_importer.tables.batch', JPATH_ADMINISTRATOR);

class ImporterApiResourceBatch extends ApiResource
{

	/*
	 * GET function fetch batches or batch based on passed param
	 *
	 * ***INPUT PARAMS***
	 * *id			- batch id (not mandatory)
	 *
	 */
	public function get()
	{
		$app 			= JFactory::getApplication();
		$jinput 		= $app->input;

		if($batchId = $jinput->get('id', 0, 'INT'))
		{
			$batch_model 	= JModelLegacy::getInstance('batch', 'ImporterModel');
			$batch = $batch_model->getItem($batchId);

			$this->plugin->setResponse($batch);
		}
		else
		{
			$batches_model 	= JModelLegacy::getInstance('batches', 'ImporterModel');
			$batches = $batches_model->getItems();

			$this->plugin->setResponse($batches);
		}
		    
	}


	/*
	 * POST function to save batch in importer_batches table
	 *
	 * ***INPUT PARAMS***
	 * *JForm[batch_name]		- object containing records.
	 * *JForm[client]
	 * *JForm[import_status]
	 * *JForm[created_date]
	 * *JForm[updated_date]
	 * *JForm[import_user]
	 * *JForm[params]
	 * 
	 */
	public function post()
	{
		$app 			= JFactory::getApplication();
		$result 		= new stdClass;
		$item_model		= JModelLegacy::getInstance('batch', 'ImporterModel');

		$postData 		= $app->input->getArray();
		$formData 		= $postData['JForm'];
		print_r($item_model->save($formData));
		die("in post function of batches");
	}
}
