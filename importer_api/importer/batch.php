<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Importer
 *
 * @copyright   Copyright (C) 2016 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

JLoader::import('components.com_importer.models.batch', JPATH_SITE);
JLoader::import('components.com_importer.models.batches', JPATH_SITE);
JLoader::import('components.com_importer.tables.batch', JPATH_ADMINISTRATOR);

/**
 * Batch Resource for Importer Plugin.
 *
 * @since  2.5
 */
class ImporterApiResourceBatch extends ApiResource
{
	/**
	 * GET function fetch batches or batch based on passed param
	 *
	 * ***INPUT PARAMS***
	 * *id			- batch id (not mandatory)
	 *
	 * @return  JSON  batch details
	 *
	 * @since  3.0
	 **/
	public function get()
	{
		$app 			= JFactory::getApplication();
		$jinput 		= $app->input;

		if ($batchId = $jinput->get('id', 0, 'INT'))
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

	/**
	 * POST function to save batch in importer_batches table
	 *
	 *  ***INPUT PARAMS***
	 * *JForm[batch_name]		- object containing records.
	 * *JForm[client]
	 * *JForm[import_status]
	 * *JForm[created_date]
	 * *JForm[updated_date]
	 * *JForm[import_user]
	 * *JForm[params]
	 *
	 * @return  JSON  success of failur status.
	 *
	 * @since  3.0
	 **/
	public function post()
	{
		$app 			= JFactory::getApplication();
		$result 		= new stdClass;
		$item_model		= JModelLegacy::getInstance('batch', 'ImporterModel');

		$postData 		= $app->input->getArray();

		$formData 		= $postData['JForm'];

		if ($formData['id'])
		{
			$formData['start_id'] = null;
		}

		$formData['start_id'] = trim(str_replace("\n", ",", $formData['start_id']), ",");

		if ($item_model->save($formData))
		{
			$this->plugin->setResponse($item_model->getState("batch.id"));
		}
		else
		{
			$this->plugin->setResponse(false);
		}
	}
}
