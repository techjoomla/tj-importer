<?php
/**
 * @package     Joomla.osian
 * @subpackage  com_osian
 *
 * @copyright   Copyright (C) 2013 - 2014 TWS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');
jimport('joomla.database.database.mysqli');
//require_once JPATH_SITE . '/components/com_importer/adapters/question.php';
require_once JPATH_SITE . '/components/com_importer/adapters/category.php';
require_once JPATH_SITE . '/components/com_importer/adapters/tmtquiz.php';
require_once JPATH_SITE . '/components/com_importer/adapters/tmtquestions.php';

/**
 * model class for import
 *
 * @package     Joomla.Iporter
 * @subpackage  com_importer
 * @since       2.5
 */
class ImporterModelimport extends JModelLegacy
{
	/**
	 * Function constructor.
	 * 
	 * @since   1.0.0
	 */
	public function __construct()
	{
		parent::__construct();

		// Set the language in the class constant
		$config		   = JFactory::getConfig();
		$this->app	   = JFactory::getApplication();
		$this->dbo	   = JFactory::getDBO();
		$this->session = JFactory::getSession();
		$this->jinput  = JFactory::getApplication()->input;
		$this->post  = $this->jinput->get('post');
	}

	/**
	 * Function getCategories retrieves  categories using buildtree class
	 *
	 * @return  array   $class_drop_down  array of 'configname/id of cats' as key and name of cat as value/
	 *
	 * @since   1.0.0
	 */
	public function getCategories()
	{
		$adapter = ucfirst($this->jinput->get('adapter'));
		$classname = $adapter . "Adapter";
		$class_drop_down = $classname::getCategories();

	//	return $class_drop_down;
		return $class_drop_down;
	}
	
		/**
	 * Function getCategories retrieves  categories using buildtree class
	 *
	 * @return  array   $class_drop_down  array of 'configname/id of cats' as key and name of cat as value/
	 *
	 * @since   1.0.0
	 */
	public function getDynamicCols()
	{
		$adapter = ucfirst($this->jinput->get('adapter'));
		$classname = $adapter . "Adapter";
		$class_drop_down = $classname::addDynamicCols();

	//	return $class_drop_down;
		return $class_drop_down;
	}

	/**
	 * Function storeBatch called from saveBasicDetails() used to save pasted data row by row by calling storeBatchDetails() in adapter.
	 *
	 * @param   array  $postdata  post data from first form view
	 * 
	 * @param   int    $adapter   adapter naem got from url
	 *
	 * @return  int    $batch_id  batch id genenrated by batch information in #__batch_details table
	 *
	 * @since   1.0.0
	 */
	public function storeBatch($postdata, $adapter)
	{
		$logged_user = JFactory::getUser();
		$date = new DateTime;

		$flag1 = new stdClass;
		$flag1->id = '';
		$flag1->batch_no = $postdata->get('batchname', '1', 'STRING');
		$flag1->created_date = date_format($date, 'Y-m-d H:i:s');
		$flag1->status = 'New';
		$flag1->filename = $postdata->get('filename', '1', 'STRING');
		$flag1->import_user = $logged_user->id;
		$flag1->publish_user = '';
		$flag1->updated_date = '';

		$this->dbo->insertObject('#__batch_details', $flag1, 'id');
		$batchid = $this->dbo->insertid();


		return $batchid;
	}

	/**
	 * Function getColumns called from saveBasicDetails() used to get columns names.
	 *
	 * @param   int  $catid    category id.
	 * 
	 * @param   int  $adapter  adapter got from url
	 *
	 * @return  array   $columns      columns array with element id as key and column name as value.
	 *
	 * @since   1.0.0
	 */
	public function getColumns($catid, $adapter)
	{
		$classname = $adapter . "Adapter";
		$columns = $classname::getColumns($catid);

		return $columns;
	}

	/**
	 * Function storeDatatoTemp called from storeCSVData() used to save pasted data row by row by calling store() in adapter.
	 *
	 * @param   array  $csvData      whole csv data
	 * 
	 * @param   int    $start_limit  starting limit of batch
	 * 
	 * @param   int    $end_limit    end limit of batch
	 * 
	 * @param   int    $batch_id     batch id got from session
	 *
	 * @return  array   $limit       limit with start and end values.
	 *
	 * @since   1.0.0
	 */
	public function storeDatatoTemp($csvData, $start_limit, $end_limit, $batch_id)
	{
		$type = $this->jinput->get('type');
		$adapter = ucfirst($this->jinput->get('adapter'));
		$batch = 2;

		if ($start_limit == 0)
		{
			$start_limit = 0;
			$end_limit = $batch;
		}

		$count = count($csvData);

		if ($count < $batch)
		{
			$data  = $csvData;
		}
		else
		{
			// Break array in batch size
			$data = array_slice($csvData, $start_limit, $batch);
			$dataa[] = $data;
		}

		foreach ($data as $fieldvalues)
		{
			$classname = $adapter . "Adapter";

			// Make validate = 1 for columns row and spare row.
			if ($fieldvalues->recordid == 'recordid')
			{
				$imported = 1;
			}
			else
			{
				$imported = 0;
			}

				$columns = $this->storeinTemp($fieldvalues, $batch_id, $imported);
		}

		$nextlstart = $end_limit;
		$nextlend = $nextlstart + $batch;

		if ($count > $nextlstart)
		{
			$limit['start'] = $nextlstart;
			$limit['end'] = $nextlend;
			$limit['subtype'] = $type;
		}
		// All batches completed, process=1
		else
		{
			$limit['start'] = "complete";
			$limit['end'] = "complete";
		}

		return $limit;
	}

	/**
	 * Function validate used to validate the pasted data..
	 *
	 * @param   array  $data      data pasted into csv row wise.
	 * 
	 * @param   int    $batch_id  batch_id of the procesing batch
	 * 
	 * @param   int    $imported  imported 1/0(in case of columns row and spare row imported we will keep it as 1)
	 *
	 * @return  return nothing
	 *
	 * @since   1.0.0
	 */
	public function storeinTemp($data, $batch_id, $imported = 0)
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
			$flag1->imported = $imported;
			$flag1->batch_id = $batch_id;
			$flag1->content_id = '';
			$this->dbo->insertObject('#__import_temp', $flag1, 'id');
		}
		elseif ($type == 'edit')
		{
			// Update records
			if ($data->recordid != 'recordid')
			{
				$recorid = $data->recordid;
				$data_to_update = json_encode($data);

				$object = new stdClass;
				$object->id = $recorid;
				$object->data = $data_to_update;

				// Update their details in the users table using id as the primary key.
				$result = $this->dbo->updateObject('#__import_temp', $object, 'id');
			}
		}

		return;
	}

	/**
	 * Function validateValues called from validateData() in controller. Calls validate function in adapter to validate values.
	 *
	 * @param   string  $batch_id     batch_id stored in session
	 *
	 * @param   string  $start_limit  starting limit sent from ajax request
	 * 
	 * @param   string  $end_limit    end limit sent from ajax request
	 * 
	 * @return  array   $limit        array of next start limit and end limit
	 *
	 * @since   1.0.0
	 */
	public function validateValues($batch_id, $start_limit, $end_limit)
	{
		$adapter = ucfirst($this->jinput->get('adapter'));
		$batch = 2;

		if ($start_limit == 0)
		{
			$i = 0;
			$start_limit = 0;
			$end_limit = $batch;
			$query_field = $this->dbo->getQuery(true);
			$query_field	->select('*')
							->from('#__import_temp')
							->where('batch_id =' . $batch_id . ' AND validated = 0');
			$this->dbo->setQuery($query_field);
			$data_to_validate  = $this->dbo->loadObjectList();
			$this->session->set('datatoValidate', '');
			$this->session->set('datatoValidate', $data_to_validate);
		}
		else
		{
			$data_to_validate = $this->session->get('datatoValidate');
		}

		$count = count($data_to_validate);

		if ($count < $batch)
		{
			$data_valid  = $data_to_validate;
		}
		else
		{
			// Below is done because our first row is column names and we need to skip it.
			if ($start_limit == 0)
			{
				$offset_start = 1;
			}
			else
			{
				$offset_start = $start_limit;
			}
			// Break array in batch size
			$data_valid = array_slice($data_to_validate, $offset_start, $batch);
			$dataa[] = $data;
		}

		foreach ($data_valid as $data)
		{
			$classname = $adapter . "Adapter";
			//die('in model');
			$invalid_array = $classname::validate(json_decode($data->data), $data->id);

			if (!empty($invalid_array))
			{
				$object = new stdClass;
				$object->id = $data->id;
				$object->invalid_columns = json_encode($invalid_array);
				$object->validated = 0;

				// Update their details in the users table using id as the primary key.
				$result = $this->dbo->updateObject('#__import_temp', $object, 'id');
			}
			else
			{
				$object = new stdClass;
				$object->id = $data->id;
				$object->invalid_columns = '';
				$object->validated = 1;

				// Update their details in the users table using id as the primary key.
				$result = $this->dbo->updateObject('#__import_temp', $object, 'id');
			}

			$i++;
		}

		$nextlstart = $end_limit;
		$nextlend = $nextlstart + $batch;

		if ($count > $nextlstart)
		{
			$limit['start'] = $nextlstart;
			$limit['end'] = $nextlend;
			$limit['count'] = $count;
			$limit['batch'] = $batch;
		}
		// All batches completed, process=1
		else
		{
			$limit['start'] = "complete";
			$limit['end'] = "complete";
		}

		return $limit;
	}

	/**
	 * Function to get invalid_data from #__import_tem
	 * 
	 * @return  array   $invalid_data  formatted array of invalid data
	 *
	 * @since   1.0.0
	 */
	public function getInvalidData()
	{
		$batch_id = $this->session->get('batch_id');
		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('id, data, invalid_columns')
						->from('#__import_temp')
						->where('batch_id =' . $batch_id . ' AND validated = 0');
		$this->dbo->setQuery($query_field);
		$invalid_data  = $this->dbo->loadObjectList();

		return $invalid_data;
	}

	/**
	 * Function to get invalid_columns from #__import_tem
	 * 
	 * @return  array   $merged_array  formatted array of invalid data
	 *
	 * @since   1.0.0
	 */
	public function getoInvalidData()
	{
		$batch_id = $this->session->get('batch_id');
		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('invalid_columns as data1')
						->from('#__import_temp')
						->where('batch_id =' . $batch_id . ' AND validated = 0');
		$this->dbo->setQuery($query_field);
		$oinvalid_data  = $this->dbo->loadColumn();
		//print_r($oinvalid_data);die('here');
		$merged_array = array();

		for ($i = 0; $i < count($oinvalid_data); $i++)
		{
			$decoded_array = json_decode($oinvalid_data[$i]);

			foreach ($decoded_array as $darray)
			{
				$row_no = $i;
				$darray->rowno = $row_no;
				$merged_array[] = json_encode($darray);
			}
		}

			return $merged_array;
	}

	/**
	 * Function to show preview data before import
	 *
	 * @param   string  $start_limit  starting limit sent from ajax request
	 * 
	 * @param   string  $end_limit    end limit sent from ajax request
	 * 
	 * @param   int     $batch_id     batch id in session
	 * 
	 * @return  array   $limit        array of next start limit and end limit
	 *
	 * @since   1.0.0
	 */
	public function showPreviewData($start_limit, $end_limit, $batch_id)
	{
		$type = $this->jinput->get('type');
		$adapter = ucfirst($this->jinput->get('adapter'));
		$batch = 2;

		if ($start_limit == 0)
		{
			$start_limit = 0;
			$end_limit = $batch;
			$query_field = $this->dbo->getQuery(true);
			$query_field	->select('*')
							->from('#__import_temp')
							->where('batch_id =' . $batch_id);
			$this->dbo->setQuery($query_field);
			$previewdata  = $this->dbo->loadObjectList();
			$this->session->set('previewdata', '');
			$this->session->set('previewdata', $previewdata);
		}
		else
		{
			$previewdata = $this->session->get('previewdata');
		}

		$count = count($previewdata);

		if ($count < $batch)
		{
			$data  = $previewdata;
		}
		else
		{
			// Break array in batch size
			$data = array_slice($previewdata, $start_limit, $batch);
			$dataa[] = $data;
		}

		$classname = $adapter . "Adapter";
		$showtitles = $classname::showpreviewTitle($fieldvalues);

		foreach ($data as $fieldvalues)
		{
			$classname = $adapter . "Adapter";
			$csdata[] = $classname::preview($fieldvalues, $showtitles);
		}

		$nextlstart = $end_limit;
		$nextlend = $nextlstart + $batch;

		if ($count >= $nextlstart)
		{
			$limit['start'] = $nextlstart;
			$limit['end'] = $nextlend;
			$limit['csvdata'] = $csdata;
		}
		// All batches completed, process=1
		else
		{
			$limit['start'] = "complete";
			$limit['end'] = "complete";
		}

		return $limit;
	}

	/**
	 * Function importData used to import data in zoo
	 *
	 * @param   string  $start_limit  starting limit sent from ajax request
	 * 
	 * @param   string  $end_limit    end limit sent from ajax request
	 * 
	 * @param   int     $batch_id     batch id in session
	 * 
	 * @return  array   $limit        array of next start limit and end limit
	 *
	 * @since   1.0.0
	 */
	public function importData($start_limit, $end_limit, $batch_id)
	{
		$type = $this->jinput->get('type');
		$adapter = ucfirst($this->jinput->get('adapter'));
		$batch = 2;

		if ($start_limit == 0)
		{
			$start_limit = 0;
			$end_limit = $batch;
			$query_field = $this->dbo->getQuery(true);
			$query_field	->select('*')
							->from('#__import_temp')
							->where('batch_id =' . $batch_id . ' AND validated = 1');
			$this->dbo->setQuery($query_field);
			$importdata  = $this->dbo->loadObjectList();
			$this->session->set('importdata', '');
			$this->session->set('importdata', $importdata);
		}
		else
		{
			$importdata = $this->session->get('importdata');
		}

		$count = count($importdata);

		if ($count < $batch)
		{
			$data  = $importdata;
		}
		else
		{
			// Break array in batch size
			$data = array_slice($importdata, $start_limit, $batch);
			$dataa[] = $data;
		}

		foreach ($data as $fieldvalues)
		{
			$classname = $adapter . "Adapter";
			$import_id = $classname::import($fieldvalues);
			$this->updateImportStatus($fieldvalues->id, $import_id, $adapter);
		}

		$nextlstart = $end_limit;
		$nextlend = $nextlstart + $batch;

		if ($count > $nextlstart)
		{
			$limit['start'] = $nextlstart;
			$limit['end'] = $nextlend;
			$limit['count'] = $count;
			$limit['csvdata'] = $csdata;
		}
		// All batches completed, process=1
		else
		{
			$limit['start'] = "complete";
			$limit['end'] = "complete";
		}

		return $limit;
	}

	/**
	 * Function to update status to 1/0 according to records imported/not/
	 *
	 * @param   int     $rowid             row id
	 * 
	 * @param   int     $imported_item_id  imported item id
	 * 
	 * @param   string  $adapter           adapter name
	 * 
	 * @return  int  1
	 *
	 * @since   1.0.0
	 */
	public function updateImportStatus($rowid, $imported_item_id,$adapter)
	{
				$object = new stdClass;
				$object->id = $rowid;

				if ($imported_item_id == 0)
				{
					$object->imported = 0;
				}
				else
				{
					$object->imported = 1;
				}

				$object->content_id = $adapter . "." . $imported_item_id;

				// Update their details in the users table using id as the primary key.
				$result = $this->dbo->updateObject('#__import_temp', $object, 'id');

				return 1;
	}

	/**
	 * Function to get link fo batch preview.
	 *
	 * @return  string  $preview_link  preview link
	 *
	 * @since   1.0.0
	 */
	public function getPreviewLink()
	{
		$batch_id = $this->session->get('batch_id');
		$adapter = ucfirst($this->jinput->get('adapter'));
		$classname = $adapter . "Adapter";
		$preview_link = $classname::getPreviewLink($batch_id);

		return $preview_link;
	}

	/**
	 * Function to get total records which are imported in batch to show in report.
	 *
	 * @return  int  $imported_count  total count of imported records
	 *
	 * @since   1.0.0
	 */
	public function getImportedCount()
	{
		$batch_id = $this->session->get('batch_id');
		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('count(id)')
						->from('#__import_temp')
						->where('batch_id =' . $batch_id . ' AND imported = 1');
		$this->dbo->setQuery($query_field);
		$imported_count  = $this->dbo->loadResult();

		return $imported_count;
	}

	/**
	 * Function to get total records which are not imported in batch to show in report.
	 *
	 * @return  int  $unimported_count  total count of not imported records
	 *
	 * @since   1.0.0
	 */
	public function getNImportedCount()
	{
		$batch_id = $this->session->get('batch_id');
		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('count(id)')
						->from('#__import_temp')
						->where('batch_id = ' . $batch_id . ' AND imported = 0');
		$this->dbo->setQuery($query_field);
		$unimported_count  = $this->dbo->loadResult();

		return $unimported_count;
	}

	/**
	 * Function to get total records in batch to show in report.
	 *
	 * @return  int  $total_count  total count
	 *
	 * @since   1.0.0
	 */
	public function getTotal()
	{
		$batch_id = $this->session->get('batch_id');
		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('count(id)')
						->from('#__import_temp')
						->where('batch_id =' . $batch_id);
		$this->dbo->setQuery($query_field);
		$total_count  = $this->dbo->loadResult();

		return $total_count;
	}
}
