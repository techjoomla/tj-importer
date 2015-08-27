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
class CategoryAdapter
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
		$catDetails = explode("/", $catid);
		$file		   = file_get_contents(JPATH_SITE . DS . 'media/zoo/applications/blog/types/' . $catDetails[0] . '.config');
		$decode		 = json_decode($file, true);
		$elements_array = array();
		$columns_array = array();
		$columns_array['recordid'] = 'recordid';
		$columns_array['asset_id'] = 'asset_id';
		$columns_array['parent_id'] = 'parent_id';
		$columns_array['lft'] = 'lft';
		$columns_array['rgt'] = 'rgt';
		$columns_array['level'] = 'level';
		$columns_array['path'] = 'path';
		$columns_array['extension'] = 'extension';
		$columns_array['title'] = 'title';
		$columns_array['alias'] = 'alias';
		$columns_array['note'] = 'note';
		$columns_array['description'] = 'description';
		$columns_array['access'] = 'access';
		$columns_array['published'] = 'published';
		$columns_array['params'] = 'params';
		$columns_array['metadata'] = 'metadata';

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
			$elements_array = $this->session->get('elements_array');

			foreach ($elements_array as $id => $eledata)
			{
				if ($eledata['type'] == 'relateditemspro')
				{
					$showtitles = 1;
				}
			}

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
			if ($imkey == 'path' || $imkey == 'alias')
			{
				if ($imval == '')
				{
					$value = '0';
				}

				$flag->$imkey = strtolower($imval);
			}
			else
			{
				$flag->$imkey = $imval;
			}
		}

		$flag->created_user_id = $user->id;
		$flag->created_time = date('Y-m-d H:i:s');
		$flag->language = '*';

		if ($value == 0)
		{
			return 0;
		}

			$this->dbo->insertObject('#__categories', $flag, 'id');
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
		$link = JRoute::_('index.php?option=com_zoo&batch=' . $batchid . '&category_id=430&lang=en&layout=preview&task=preview&view=preview');

		return $link;
	}
}
