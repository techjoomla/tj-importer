<?php
/**
 * @package     Joomla.osian
 * @subpackage  com_osian
 *
 * @copyright   Copyright (C) 2013 - 2014 TWS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';

/**
 * Class to process all things related to import
 *
 * @package     Joomla.Osian
 * @subpackage  com_osian
 * @since       2.5
 */
class OsianAdapter
{
	/**
	 * Function constructor.
	 * 
	 * @param   int  &$subject  subject
	 * 
	 * @param   int  $config    config
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
		$this->jinput  = JFactory::getApplication()->input;
	}

	/**
	 * Function to store details in #__batch_details table
	 *
	 * @param   array  $data  post data from form
	 *
	 * @return  int     $batchid  generated batch id after inserting record.
	 *
	 * @since   1.0.0
	 */
	 // To model
	/*public function storeBatchDetails($data)
	{
		$logged_user = JFactory::getUser();
		$date = new DateTime;
		$flag1 = new stdClass;
		$flag1->id = '';
		$flag1->batch_no = $data->get('batchname', '1', 'STRING');
		$flag1->created_date = date_format($date, 'Y-m-d H:i:s');
		$flag1->status = 'New';
		$flag1->filename = $data->get('filename', '1', 'STRING');
		$flag1->import_user = $logged_user->id;
		$flag1->publish_user = '';
		$flag1->updated_date = '';

		$this->dbo->insertObject('#__batch_details', $flag1, 'id');
		$batchid = $this->dbo->insertid();

		return $batchid;
	}*/

	/**
	 * Function to get columns according to the category selected.
	 *
	 * @param   int  $catid  category id in format category config file/catid.
	 *
	 * @return  arrray  $columns_array  return columns array.
	 *
	 * @since   1.0.0
	 */
	public function getColumns($catid)
	{
		$catDetails = explode("/", $catid);
		$file		   = file_get_contents(JPATH_SITE . DS . 'media/zoo/applications/blog/types/' . $catDetails[0] . '.config');
		$decode		 = json_decode($file, true);
		$elements_array = array();
		$columns_array = array();

		// Get columns for name, category and alias/
		$elements_array['recordid'] = $columns_array['recordid'] = 'recordid';
		$elements_array['name'] = $columns_array['name'] = 'name';
		$elements_array['alias'] = $columns_array['alias'] = 'alias';
		$elements_array['cateogry'] = $columns_array['category'] = 'cateogry';

		foreach ($decode['elements'] as $k => $ele)
		{
				$option = $decode['elements'][$k]['option'];

				if ($option)
				{
					for ($i = 0; $i <= count($option); $i++)
					{
						$opt_arr[$k][$i] = $option[$i]['value'];
					}

					$opt_arr[$k] = array_filter($opt_arr[$k], 'strlen');
				}

				$elements_array[$k] = $ele;
				$columns_array[$k] = $ele['name'];
		}

		$this->session->set('elements_array', $elements_array);
		$this->session->set('opt_array', $opt_arr);

		return $columns_array;
	}

	/**
	 * Function validate used to validate the pasted data..
	 *
	 * @param   array  $data      data pasted into csv row wise.
	 * 
	 * @param   int    $batch_id  batch_id of the procesing batch
	 *
	 * @return  return nothing
	 *
	 * @since   1.0.0
	 */
	 	 // To model

	/*public function store($data, $batch_id)
	{
		$type = $this->jinput->get('type');
		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('*')
						->from('#__batch_details')
						->where('id =' . $batch_id);
		$this->dbo->setQuery($query_field);
		$batch_info  = $this->dbo->loadObject();
		if ($type == 'add')
		{
			$flag1 = new stdClass;
			$flag1->id = '';
			$flag1->data = json_encode($data);
			$flag1->title = $batch_info->batch_no;
			$flag1->validated = 0;
			$flag1->imported = 0;
			$flag1->batch_id = $batch_id;
			$flag1->content_id = '';
			$this->dbo->insertObject('#__import_temp', $flag1, 'id');
		}
		else if ($type == 'edit')
		{
			// Update records
			//print_r($data);die('mydata');
			if ($data->recordid != 'recordid')
			{
				$recorid = $data->recordid;
				$data_to_update = json_encode($data);
				$query = "UPDATE #__import_temp SET data = ".$data_to_update." WHERE id = " .$recorid;
				$this->dbo->setQuery($query);
				$this->dbo->query();
			}
			
		}

		return;
	}*/

	/**
	 * Function validate used to validate the pasted data..
	 *
	 * @param   array  $data  data from one row from #__import_temp table.
	 *
	 * @return  returrn row validated 1/0
	 *
	 * @since   1.0.0
	 */
	public function validate($data, $rowid)
	{
		$elements_array = $this->session->get('elements_array');
		$opt_arr = $this->session->get('opt_array');
		$flag = 0;
		$invalid_array = array();
		foreach ($data as $datakey=>$datavalue)
		{
			if ($datavalue!= '')
			{
			//print_r($elements_array[$datakey]);
			if ($datakey == 'alias')
			{
				$query_field = $this->dbo->getQuery(true);
				$query_field->select('id, alias')
							->from('#__zoo_item')
							->where('alias = ' . $this->dbo->quote($datavalue));
				$this->dbo->setQuery($query_field);
				$newalias   = $this->dbo->loadObject();
				
				// If alias already exists
				if ($newalias->id != '')
				{
					$flag = 1;
					$invalid_array[$datakey]=$datavalue;
				}
			
			}
			else if($datakey == 'category')
			{
			}
			else if($elements_array[$datakey]['type'] == 'select' || $elements_array[$datakey]['type'] == 'radio')
			{
				if (!in_array($datavalue, $opt_data[$datakey]) && $datavalue!='')
				{
					//print_r($elements_array[$datakey]);
					//print_r($opt_data);die;
					//echo "invalid"."--".$datavalue."--".$opt_data[$dataval];die;
					$flag = 1;
					$invalid_array[$datakey]=$datavalue;
				}
				//echo "valid ".$dataval;die;
			}
			else if($elements_array[$datakey]['type'] == 'relateditemspro')
			{
			
			}
			}
		}
		return $invalid_array;
	}
	public function preview($data, $showtitles = 0)
	{
		return $data;
	}
	
	public function import($data)
	{
		
		$imdata = json_decode($data->data);
		
		$elements_array = $this->session->get('elements_array');
		//print_r($elements_array);die('data');
		$user_id = JFactory::getUser();
		$table = $this->zapp->table->item;
		//check if alias exists
		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('id')
		->from('#__zoo_items')
		->where('alias ="' . $imdata->alias . '"');
		$this->dbo->setQuery($query_field);
		$alias = $this->dbo->loadResult();
		if($alias !='')
		{
			return 0;
		}
		// Create item 
		$item = $this->zapp->object->create('Item');
		$item->alias = $imdata->alias;
		$item->name = $imdata->name;
		$item->type = strtolower($imdata->category);
		$query_field = $this->dbo->getQuery(true);

		// Get id of that new category
		$query_field	->select('id')
		->from('#__zoo_category')
		->where('alias ="' . $imdata->category . '"');
		$this->dbo->setQuery($query_field);
		$cid = $this->dbo->loadResult();
		//$application = $this->zapp->zoo->getApplication();
		$id = 1;
		$application = $this->zapp->table->application->get($id);
		
		// fix access if j16
		if ($this->zapp->joomla->version->isCompatible('1.6') && $item->access == 0) {
			$item->access = $this->zapp->joomla->getDefaultAccess();
		}
		// store application id
		$item->application_id = $application->id;
		$item->created = $this->zapp->date->create()->toSQL();
		$item->publish_up = $this->zapp->date->create()->toSQL();
		// if author is unknown set current user as author
		if (!$item->created_by) {
			$item->created_by = $user_id->id;
		}
		// store modified_by
		$item->modified_by = $user_id->id;
		$item->modified = $this->zapp->date->create()->toSQL();
		// set metadata, content, config params
		//$item->getParams()->set('metadata.', @$item['metadata']);
		//$item->getParams()->set('content.', @$item['content']);
		//$item->getParams()->set('config.', @$item['config']);
		//print_r($item);die('ff');
		$item->elements = $this->zapp->data->create();
		// set params
		$item->getParams()
			->remove('metadata.')
			->remove('template.')
			->remove('content.')
			->remove('config.')
			->set('metadata.', '')
			->set('template.', '')
			->set('content.', '')
			->set('config.', '')
			->set('config.enable_comments', '')
			->set('config.primary_category', $cid);
		//$name='name';
		//echo $imdata->$name;die('gf');
		//$data = $item_obj->elements;
		//$elements = json_decode($data, true);
		//$elements = $item->getElements();
		/*foreach ($item->getElements() as $id => $element) {
				//print_r($id);
			
				if (isset($imdata->id)) {
					if ($id == 'recordid' || $id == 'name' || $id == 'alias' || $id == 'category')
					{
						 $type =$id;
					}
					else
					{
						$type =$elements_array[$id]['type'] ;
					}
					switch ($type) {
				case 'text':
				//$element->bindData($imdata->id);
				$element_data = array();
				$element_data[0]['value'] = $imdata->id;
				$element->bindData($element_data);
				case 'textarea':
				$element_data = array();
				$element_data[0]['value'] = $imdata->id;
				$element->bindData($element_data);
				case 'link':
				case 'email':
				case 'date':
					
					$element->bindData($imdata->id);
					break;
				case 'imagepro':
					$element_data = array();
					$images = explode('|',$imdata->id);
					$i = 0;
					foreach($images as $img)
					{
						$img_ele[] = array('file'=>$img, 
											'title' => '', 
											'file2' => '',
											'spotlight_effect' => '', 
											'caption' => ''
											);
						$i++;
					}
					//print_r($ele); die;
					$element->bindData($img_ele);
					break;
				case 'country':
					$element_data = array();
					$element_data['country'][] = $imdata->id;
					$element->bindData($element_data);
					break;
				case 'select':
					$element_data = array();
					$element_data['option'][] = $imdata->id;
					$element->bindData($element_data);
				case 'radio':
					$element_data = array();
					$element_data['option'][] = $imdata->id;
					$element->bindData($element_data);
				case 'checkbox':
					$element_data = array();
					$element_data['option'][] = $imdata->id;
					$element->bindData($element_data);
					break;
				case 'gallery':
					$datavalue = trim($imdata->id, '/\\');
					$element->bindData(array('value' => $datavalue));
					break;
				case 'image':
				case 'download':
					$element->bindData(array('file' => $imdata->id));
					break;
				case 'googlemaps':
					$element->bindData(array('location' => $imdata->id));
					break;
			}
					//$element->bindData($imdata->id);
				} else {
					$element->bindData();
				}
			}*/
			//print_r($item);die('fgf');
			$table->save($item);
			$categories[] = $cid;
			$this->zapp->category->saveCategoryItemRelations($item, $categories);
		//print_r($item);die('ff');
		/*foreach ($imdata as $datakey=>$datavalue)
		{
			if ($datakey == 'recordid' || $datakey == 'name' || $datakey == 'alias' || $datakey == 'category')
			{
				 $type =$datakey;
			}
			else
			{
				$type =$elements[$datakey]['type'] ;
			}
			switch ($type) {
				case 'text':
				$element_data = array();
				$element_data['value'] = $datavalue;
				$elements[$datakey]->bindData($element_data);
				case 'textarea':
					$element_data = array();
				$element_data['value'] = $datavalue;
				$elements[$datakey]->bindData($element_data);
				case 'link':
				case 'email':
				case 'date':
					
					$elements[$datakey]->bindData($datavalue);
					break;
				case 'imagepro':
					$element_data = array();
					$images = explode('|',$datavalue);
					$i = 0;
					foreach($images as $img)
					{
						$img_ele[] = array('file'=>$img, 
											'title' => '', 
											'file2' => '',
											'spotlight_effect' => '', 
											'caption' => ''
											);
						$i++;
					}
					//print_r($ele); die;
					$elements[$datakey]->bindData($img_ele);
					break;
				case 'country':
					$element_data = array();
					$element_data['country'][] = $datavalue;
					$elements[$datakey]->bindData($element_data);
					break;
				case 'select':
					$element_data = array();
					$element_data['option'][] = $datavalue;
					$elements[$datakey]->bindData($element_data);
				case 'radio':
					$element_data = array();
					$element_data['option'][] = $datavalue;
					$elements[$datakey]->bindData($element_data);
				case 'checkbox':
					$element_data = array();
					$element_data['option'][] = $datavalue;
					$elements[$datakey]->bindData($element_data);
					break;
				case 'gallery':
					$datavalue = trim($datavalue, '/\\');
					$elements[$datakey]->bindData(array('value' => $datavalue));
					break;
				case 'image':
				case 'download':
					$elements[$datakey]->bindData(array('file' => $datavalue));
					break;
				case 'googlemaps':
					$elements[$datakey]->bindData(array('location' => $datavalue));
					break;
			}
		}*/
		print_r($item);die('iosds');
	}
}
