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

JLoader::import('components.com_importer.models.batch', JPATH_ADMINISTRATOR);
JLoader::import('components.com_importer.models.batches', JPATH_ADMINISTRATOR);
JLoader::import('components.com_importer.tables.batch', JPATH_ADMINISTRATOR);

/**
 * Batch Resource for Importer Plugin.
 *
 * @since  2.5
 */
class ImporterApiResourceBatches extends ApiResource
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

		$clientApp		= $jinput->get('clientapp', '', 'STRING');

		if ($clientApp)
		{
			$batches_model 	= JModelLegacy::getInstance('batches', 'ImporterModel');
			$batches_model->setState('filter.client', $clientApp);
			$batches		= $batches_model->getItems();

			foreach ($batches as $key => $batch)
			{
				$createdUser = JFactory::getUser($batch->created_user);
				$batches[$key]->created_user = $createdUser->name . " (" . $createdUser->username . ")";

				$batches[$key]->created_date = JHtml::date($batch->created_date, 'd M Y - h:i a');
				$batches[$key]->updated_date = (($batch->updated_date == '0000-00-00 00:00:00') ?
												$batch->updated_date :
												JHtml::date($batch->updated_date, 'd M Y - h:i a'));
			}

			$returnData = array(
								'batches' => $batches,
								'totalBatches' => $batches_model->getTotal()
							);

			$this->plugin->setResponse($returnData);
		}
		else
		{
			die("No client app is provided");
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
	}
}
