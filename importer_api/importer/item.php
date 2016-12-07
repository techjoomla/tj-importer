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

JLoader::import('components.com_importer.models.item', JPATH_ADMINISTRATOR);
JLoader::import('components.com_importer.tables.item', JPATH_ADMINISTRATOR);

class ImporterApiResourceItem extends ApiResource
{

	public function get()
	{
		die("in batch get");	 
	}

	public function post()
	{

		$this->plugin->setResponse($this->importItem());
		die("\nin batch post");
	}

	public function importItem()
	{
		$app 			= JFactory::getApplication();
		$result 		= new stdClass;
		$item_model 	= JModelLegacy::getInstance('item', 'ImporterModel');

// 		print_r($_GET);
 		$postData = $app->input->getArray();
 		$formData = $postData['JForm'];
 		print_r($item_model->save($formData));
 		
	}

}
