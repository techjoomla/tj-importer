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
require_once JPATH_SITE . '/components/com_osian/classes/build_hierarchy.php';

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
	 * @since   1.0.0
	 */
	public function __construct()
	{
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
	 * Function to get categories for dropdown.
	 *
	 * @return  arrray  $class_drop_down  array of dropdown values (categories)
	 *
	 * @since   1.0.0
	 */
	public function getCategories()
	{
		$obj				 = new Build_Hierarchy;
		$all_classifications = $obj->BuildTree();
		$class_drop_down	 = array();

		foreach ($all_classifications as $key => $value)
		{
			if ($value['parent'] === 0 && $value['name'] != '')
			{
				$class_drop_down[$value['config'] . "/" . $value['id']] = $value['name'];
			}
		}

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
		$file		   = file_get_contents(JPATH_SITE . '/media/zoo/applications/blog/types/' . $catDetails[0] . '.config');
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

		$this->session->set('elements_array', '');
		$this->session->set('elements_array', $elements_array);
		$this->session->set('opt_array', '');
		$this->session->set('opt_array', $opt_arr);

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
		$elements_array = $this->session->get('elements_array');
		$opt_arr = $this->session->get('opt_array');
		$flag = 0;
		$invalid_array = array();
		$i = 0;
		$j = 0;

		if (!empty($data))
		{
		foreach ($data as $datakey => $datavalue)
		{
				if ($datakey == 'alias')
				{
					if ($datavalue != '')
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
							$invalid_array[$i]['element_id']   = $datakey;
							$invalid_array[$i]['value'] = $datavalue;
							$i++;
						}
					}
				}
				elseif($datakey == 'category')
				{
					/*if ($datavalue == '')
					{
						$flag = 1;
						$invalid_array[$i]['element_id']   = $datakey;
						$invalid_array[$i]['value'] = $datavalue;
						$i++;
					}*/
				}
				elseif($datakey == 'name')
				{
					/*if ($datavalue == '')
					{
						$flag = 1;
						$invalid_array[$i]['element_id']   = $datakey;
						$invalid_array[$i]['value'] = $datavalue;
						$i++;
					}*/
				}
				elseif ($elements_array[$datakey]['type'] == 'select' || $elements_array[$datakey]['type'] == 'radio')
				{
					if (!in_array($datavalue, $opt_arr[$datakey]) && $datavalue != '')
					{
						$flag = 1;
						$invalid_array[$i]['element_id']   = $datakey;
						$invalid_array[$i]['value'] = $datavalue;
						$i++;
					}
				}
				elseif ($elements_array[$datakey]['type'] == 'relateditemspro')
				{
					if ($datavalue != '')
					{
					$flag = 0;
					$ripro_vals = explode("|", $datavalue);

					foreach ($ripro_vals as $nv)
					{
						$query_field = $this->dbo->getQuery(true);
						$query_field->select('name')
									->from('#__zoo_item')
									->where('alias = ' . $this->dbo->quote($nv));
						$this->dbo->setQuery($query_field);
						$mname   = $this->dbo->loadResult();

						// Means it is invalid
						if ($mname == '')
						{
							$flag = 1;
							break;
						}
					}

					if ($flag == 1)
					{
						$invalid_array[$i]['element_id']   = $datakey;
						$invalid_array[$i]['value'] = $datavalue;
					}

					$i++;
					}
				}
				else
				{
					// Do nothing
				}

				$j++;
		}
		}

		$i++;

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
		$rowdata = json_decode($data->data);
		$elements_array = $this->session->get('elements_array');

		if ($rowdata->name != 'name')
		{
			foreach ($rowdata as $datakey => $datavalue)
			{
				if ($elements_array[$datakey]['type'] == 'relateditemspro' && $datavalue != '')
				{
					$mname = array();
					$ripro_vals = array();
					$ripro_vals = explode("|", $datavalue);

					foreach ($ripro_vals as $nv)
					{
						$query_field = $this->dbo->getQuery(true);
						$query_field->select('name')
									->from('#__zoo_item')
									->where('alias = ' . $this->dbo->quote($nv));
						$this->dbo->setQuery($query_field);
						$mname[]   = $this->dbo->loadResult();
					}

					$ri_names = implode("|", $mname);
					$rowdata->$datakey = $ri_names;
					unset($ri_names);
				}
			}

			$rowdata_encode = json_encode($rowdata);
			$data->data = $rowdata_encode;
		}

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
		$elements_array = $this->session->get('elements_array');
		$user_id = JFactory::getUser();
		$table = $this->zapp->table->item;

		// Check if alias exists
		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('id')
		->from('#__zoo_item')
		->where('alias ="' . strtolower($imdata->alias) . '"');
		$this->dbo->setQuery($query_field);
		$calias = $this->dbo->loadResult();

		// If alias exists  return
		if ($calias != '')
		{
			return 0;
		}
		// Create item
		$item = $this->zapp->object->create('Item');
		$item->alias = strtolower($imdata->alias);
		$item->name = $imdata->name;
		$cats = explode("/", $imdata->category);

		// Means it is a masterlist
		if (count($cats) == 1)
		{
			$item->type = strtolower($cats[0]);
			$item->state = 1;
			$cattype = $cats[0];
		}
		else
		{
			$item->type = strtolower($cats[0]);
			$cattype = $cats[1];
			$item->state = 0;
		}

				// If type blank  return
		if ($item->type == '')
		{
			return 0;
		}

		$query_field = $this->dbo->getQuery(true);

		// Get id of that new category
		$query_field->select('id')
		->from('#__zoo_category')
		->where('alias = "' . $cattype . '" OR name = "' . $cattype . '"');
		$this->dbo->setQuery($query_field);
		$cid = $this->dbo->loadResult();
		$id = 1;
		$application = $this->zapp->table->application->get($id);

		// Fix access if j16
		if ($this->zapp->joomla->version->isCompatible('1.6') && $item->access == 0)
		{
			$item->access = $this->zapp->joomla->getDefaultAccess();
		}

		// Store application id
		$item->application_id = $application->id;
		$item->created = $this->zapp->date->create()->toSQL();
		$item->publish_up = $this->zapp->date->create()->toSQL();

		// If author is unknown set current user as author
		if (!$item->created_by)
		{
			$item->created_by = $user_id->id;
		}
		// Store modified_by
		$item->modified_by = $user_id->id;
		$item->modified = $this->zapp->date->create()->toSQL();
		$item->elements = $this->zapp->data->create();

		// Set params
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

			foreach ($item->getElements() as $id => $element)
			{
				$type = $elements_array[$id]['type'];

				// Text
				switch ($type)
				{
				case 'text':
					$arr = array();
					$arr[0]['value'] = $imdata->$id;
					$element->bindData($arr);
				break;
				case 'radio':
					$arr = array();
					$opt['option'][0] = $imdata->$id;
					$element->bindData($opt);
					break;
				case 'select':
					$arr = array();
					$opt['option'][0] = $imdata->$id;
					$element->bindData($opt);
					break;
				case 'textarea':
					$arr = array();
					$arr[0]['value'] = $imdata->$id;
					$element->bindData($arr);
				case 'imagepro':
					$img_ele = array();
					$images = explode('|', $imdata->$id);
					$i = 0;

					foreach ($images as $img)
					{
						$img_ele[] = array('file' => $img,
											'title' => '',
											'file2' => '',
											'spotlight_effect' => '',
											'caption' => ''
											);
						$i++;
					}

					$element->bindData($img_ele);
					break;
				case 'country':
					$arr = array();
					$arr['country'][0] = $imdata->$id;
					$element->bindData($arr);
					break;
				case 'relateditemspro':
					$element->bindData();
				}
			}

			$table->save($item);
			$categories[] = $cid;
			$this->zapp->category->saveCategoryItemRelations($item, $categories);

			// Save RI-pro items
			$item_details 	= $this->zapp->table->item->get($item->id);

			foreach ($imdata as $imdata_key => $imdata_val)
			{
				$type = $elements_array[$imdata_key]['type'];

				if ($type == 'relateditemspro')
				{
					if ($imdata_val != '')
					{
						$RI_Records = explode('|', $imdata_val);

						foreach ($RI_Records as $rec)
						{
							$query_field = $this->dbo->getQuery(true);
							$query_field	->select('id')
							->from('#__zoo_item')
							->where('alias ="' . $rec . '"');
							$this->dbo->setQuery($query_field);
							$ri_id = $this->dbo->loadResult();

							if ($ri_id)
							{
								$flag = new stdClass;
								$flag->id = '';
								$flag->item_id = $item_details->id;
								$flag->ritem_id = $ri_id;
								$flag->element_id = trim($imdata_key);
								$this->dbo->insertObject('#__zoo_relateditemsproxref', $flag, 'id');
							}

							unset($ri_id);
						}
					}
				}
			}

			/* TODO : REMOVE IT .This is done for handling old batch preview tables */
				$batch_id = $this->session->get('batch_id');
				$data = new stdClass;
				$data->id = '';
				$data->batch_no = $batch_id;
				$data->record_id = $item_details->id;
				$this->dbo->insertObject('#__batch_item_xref', $data, 'id');
			/* END - This  is done for handling old batch preview tables */

			return $item_details->id;
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

	/**
	 * Function to add dynamic field in first form. Add an array and it will show you that type of field..
	 *
	 * @return  return fields
	 *
	 * @since   1.0.0
	 */
	public function addDynamicCols()
	{
		$fields = array();

		return $fields;
	}
}
