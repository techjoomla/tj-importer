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


/**
 * Class to process all things related to import
 *
 * @package     Joomla.Osian
 * @subpackage  com_osian
 * @since       2.5
 */
class TmtquizAdapter
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
		$this->session = JFactory::getSession();
		$this->jinput  = JFactory::getApplication()->input;
	}

	/**
	 * Function to get categories for dropdown.
	 *
	 * @return  arrray  $class_drop_down  array of dropdown values (categories)
	 *
	 * @since   1.0.0
	 */
	public function getCategories()
	{
		$class_drop_down = 0;

		return $class_drop_down;
	}

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
		$columns_array = array();
		$columns_array['recordid'] = 'recordid';
		$columns_array['title'] = 'title';
		$columns_array['description'] = 'description';
		$columns_array['state'] = 'published';
		$columns_array['show_time'] = 'Show Time Countdown';
		$columns_array['time_duration'] = 'Time Duration(in minutes) ';
		$columns_array['show_time_finished'] = 'Show Time Finished Alert';
		$columns_array['time_finished_duration'] = 'Minutes Before Showing Time Finished Alert';
		$columns_array['total_marks'] = 'Total Marks';
		$columns_array['passing_marks'] = 'Minimum Marks To Pass The Quiz';
		$columns_array['notify_candidate_passed'] = 'Notify candidate passed';
		$columns_array['notify_candidate_failed'] = 'Notify candidate failed';
		$columns_array['notify_admin'] = 'Notify Admin';
		$columns_array['start_date'] = 'Start Date';
		$columns_array['end_date'] = 'End Date';
		$columns_array['isObjective'] = 'Is Objective';
		$columns_array['resume'] = 'Resume Quiz';
		$columns_array['termscondi'] = 'Show terms and conditions';
		$columns_array['answer_sheet'] = 'Show answer sheet';

		return $columns_array;
	}

	/**
	 * Function validate used to validate the pasted data..
	 *
	 * @param   array  $data   data from one row from #__import_temp table.
	 *
	 * @param   int    $rowid  rowid.
	 *
	 * @return  returrn row validated 1/0
	 *
	 * @since   1.0.0
	 */
	public function validate($data, $rowid)
	{
		$invalid_array = array();

		return $invalid_array;
	}

	/**
	 * Function to set showpreviewTitle yes/no
	 *
	 * @return  return $showtitles  1/0
	 *
	 * @since   1.0.0
	 */
	public function showpreviewTitle()
	{
			$showtitles = 0;

			return $showtitles;
	}

	/**
	 * Function to build preview.
	 *
	 * @param   array  $data        per row data from #__import_temp table.
	 *
	 * @param   int    $showtitles  id ri pro items are there then showtitles 1 else o
	 *
	 * @return  return $data  updated data
	 *
	 * @since   1.0.0
	 */
	public function preview($data, $showtitles = 0)
	{
		return $data;
	}

	/**
	 * Function to get import records in zoo.
	 *
	 * @param   array  $data  per row data from #__import_temp table.
	 *
	 * @return  return $item_details->id imported item id
	 *
	 * @since   1.0.0
	 */
	public function import($data)
	{
		// Decode data in import_temp
		$imdata = json_decode($data->data);
		$user = JFactory::getUser();
		$value = 1;
		$flag = new stdClass;

		foreach ($imdata as $imkey => $imval)
		{
			$flag->$imkey = $imval;

			if ($imkey == 'title' && $imval == '')
			{
				$value = 0;
			}
		}

		if ($value == 0)
		{
			return 0;
		}

		$flag->checked_out = 0;
		$flag->checked_out_time = '';
		$flag->created_by = $user->id;
		$flag->reviewers = $user->id;
		$flag->created_on = date('Y-m-d H:i:s');
		$this->dbo->insertObject('#__tmt_tests', $flag, 'id');
		$insert_id = $this->dbo->insertid();

		return $insert_id;
	}

	/**
	 * Function to get preview link for checking imported records.
	 *
	 * @param   int  $batchid  id of the batch imported.
	 *
	 * @return  return link
	 *
	 * @since   1.0.0
	 */
	public function getPreviewLink($batchid)
	{
		return $link;
	}

	/**
	 * Function to add any dynamic column
	 *
	 * @return  array $fields
	 *
	 * @since   1.0.0
	 */
	public function addDynamicCols()
	{
		$fields = array();

		return $fields;
	}
}
