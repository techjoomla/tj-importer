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
require_once JPATH_SITE . '/components/com_importer/adapters/osian.php';
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
		$this->params  = $this->app->getParams('com_importer');
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

	/* Following code is done purely for Osian */
		$data = new stdClass;
		$data->id = '';
		$data->batch_no = $batchid;
		$data->created_date = date_format($date, 'Y-m-d H:i:s');
		$data->status = 'New';
		$data->filename = $postdata->get('filename', '1', 'STRING');
		$data->import_user = $logged_user->id;
		$data->publish_user = 0;

		$this->dbo->insertObject('#__batch_info', $data, id);
		unset($data);
		/* END_-Following code is done purely for Osian */
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
		$batch = $this->params->get('import_batch_limit');

		if ($start_limit == 0)
		{
			$start_limit = 0;
			$end_limit = $batch;
		}

		// CSV data empty means records are completed. return with complete status
		if (empty($csvData))
		{
			$limit['start'] = "complete";
			$limit['end'] = "complete";

			return $limit;
		}

		foreach ($csvData as $fieldvalues)
		{
			$classname = $adapter . "Adapter";

			// Make validate = 1 for columns row and spare row.
			if ($fieldvalues->name == '' || $fieldvalues->recordid == 'recordid')
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

		$limit['start'] = $nextlstart;
		$limit['end'] = $nextlend;
		$limit['subtype'] = $type;

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
		$batch = $this->params->get('import_batch_limit');
		$query_field = $this->dbo->getQuery(true);
		$invalid_array = array();

		if ($start_limit == 0)
		{
			$i = 0;
			$start_limit = 0;
			$end_limit = $batch;
			$query = $this->dbo->getQuery(true);
			$query->select('COUNT(*)')
						->from('#__import_temp')
						->where('batch_id =' . $batch_id . ' AND validated = 0');
			$this->dbo->setQuery($query);
			$count  = $this->dbo->loadResult();

			$this->session->set('ccount', "");
			$this->session->set('ccount', $count);

			// Start limit is 0 then get first 2 rows
			$query_field	->select('*')
						->from('#__import_temp')
						->where('batch_id =' . $batch_id . ' AND validated = 0')
						->setLimit($batch, $start_limit);
		}
		else
		{
			// When limit is next then get last processed id and get next number of records. This was done to avoid batch sequence collpase
			$last_processed_id = $this->session->set('last_processed_id');
			$query_field	->select('*')
						->from('#__import_temp')
						->where('batch_id =' . $batch_id . ' AND validated = 0 AND id >' . $last_processed_id)
						->setLimit($batch);
		}

		// Process query
		$this->dbo->setQuery($query_field);
		$data_to_validate  = $this->dbo->loadObjectList();
		$count = $this->session->get('ccount');

		// First row is column names so unset that
		if ($start_limit == 0)
		{
			unset($data_to_validate{0});
		}

		// Query result empty means all records processed
		if (empty($data_to_validate))
		{
			$limit['start'] = "complete";
			$limit['end'] = "complete";

			return $limit;
		}

		$last_processed_id = 0;

		foreach ($data_to_validate as $data)
		{
			$classname = $adapter . "Adapter";
			$class_object = new $classname;
			$invalid_array = $class_object->validate(json_decode($data->data), $data->id);

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
			$last_processed_id = $data->id;
			$this->session->set('last_processed_id', '');
			$this->session->set('last_processed_id', $last_processed_id);
		}

		$nextlstart = $end_limit;
		$nextlend = $nextlstart + $batch;

		$limit['start'] = $nextlstart;
		$limit['end'] = $nextlend;
		$limit['count'] = $count;
		$limit['batch'] = $batch;
		$limit['start_limit'] = $start_limit;

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
		$adapter = $this->jinput->get('adapter');
		$batch = $this->params->get('import_batch_limit');

		if ($start_limit == 0)
		{
			$start_limit = 0;
			$end_limit = $batch;
		}
		else
		{
			// Do nothing
		}

		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('*')
						->from('#__import_temp')
						->where('batch_id =' . $batch_id)
						->setLimit($batch, $start_limit);
		$this->dbo->setQuery($query_field);
		$previewdata  = $this->dbo->loadObjectList();
		$count = count($previewdata);

		// Query result empty means all records processed
		if (empty($previewdata))
		{
			$limit['start'] = "complete";
			$limit['end'] = "complete";

			return $limit;
		}

		$classname = $adapter . "Adapter";
		$showtitles = $classname::showpreviewTitle($fieldvalues);

		foreach ($previewdata as $fieldvalues)
		{
			$classname = $adapter . "Adapter";
			$csdata[] = $classname::preview($fieldvalues, $showtitles);
		}

		$nextlstart = $end_limit;
		$nextlend = $nextlstart + $batch;

		$limit['start'] = $nextlstart;
		$limit['end'] = $nextlend;
		$limit['csvdata'] = $csdata;

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
		$adapter = $this->jinput->get('adapter');
		$batch = $this->params->get('import_batch_limit');

		if ($start_limit == 0)
		{
			$start_limit = 0;
			$end_limit = $batch;
			$query = $this->dbo->getQuery(true);
			$query->select('COUNT(*)')
						->from('#__import_temp')
						->where('batch_id =' . $batch_id . ' AND validated = 1');
			$this->dbo->setQuery($query);
			$count  = $this->dbo->loadResult();
			$this->session->set('icount', "");
			$this->session->set('icount', $count);
			/*$query_field = $this->dbo->getQuery(true);
			$query_field	->select('*')
							->from('#__import_temp')
							->where('batch_id =' . $batch_id . ' AND validated = 1');
			$this->dbo->setQuery($query_field);
			$importdata  = $this->dbo->loadObjectList();
			$this->session->set('importdata', '');
			$this->session->set('importdata', $importdata);*/
		}
		else
		{
			// $importdata = $this->session->get('importdata');
		}

		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('*')
						->from('#__import_temp')
						->where('batch_id =' . $batch_id . ' AND validated = 1')
						->setLimit($batch, $start_limit);
		$this->dbo->setQuery($query_field);
		$importdata  = $this->dbo->loadObjectList();
		$count = $this->session->get('icount');

		// Query result empty means all records processed
		if (empty($importdata))
		{
			$limit['start'] = "complete";
			$limit['end'] = "complete";

			return $limit;
		}

		foreach ($importdata as $fieldvalues)
		{
			$classname = $adapter . "Adapter";
			$class_object = new $classname;

			if (!empty($fieldvalues))
			{
				$import_id = $class_object->import($fieldvalues);
			}

			$this->updateImportStatus($fieldvalues->id, $import_id, $adapter);
		}

		$nextlstart = $end_limit;
		$nextlend = $nextlstart + $batch;

		$limit['start'] = $nextlstart;
		$limit['end'] = $nextlend;
		$limit['count'] = $count;

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
