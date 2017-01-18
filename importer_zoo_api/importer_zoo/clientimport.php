<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Importer_zoo
 *
 * @copyright   Copyright (C) 2016 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

// Load ZOO config
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/zoo.php';
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/controllers/item.php';

/**
 * Clientcolumns Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_ZooApiResourceClientimport extends ApiResource
{
	/**
	 * GET function to fetch columns in zoo
	 *
	 * @return  JSON  types details
	 *
	 * @since  3.0
	 **/
	public function get()
	{
		die("inside get");
	}

	/**
	 * POST function unnecessary
	 *
	 * @return  STRING  error message
	 * 
	 * @since  3.0
	 **/
	public function post()
	{
		$this->dbo		= JFactory::getDBO();
		$this->jinput	= $jinput		= JFactory::getApplication()->input;
		$records		= $jinput->get('records', '', 'RAW');
		$batch			= $jinput->get('batchDetails', '', 'STRING');

		$this->user		= JFactory::getUser();
		$this->records	= json_decode($records);
		$this->batch	= json_decode($batch);
		$newIds			= array();

		$type			= $this->batch->params->type;
		$filePath		= JPATH_SITE . '/media/zoo/applications/blog/types/' . $type . '.config';

		if (JFile::exists($filePath))
		{
			$decodeFile		= (array) json_decode(JFile::read($filePath));
			$this->decodeElements = (array) $decodeFile['elements'];
		}

		// Get ZOO App instance
		$this->zapp	= App::getInstance('zoo');

		foreach ($this->records as $record)
		{
			if (empty(array_filter((array) $record)))
			{
				continue;
			}

			// Save function call
			$newId = $this->saveRec($record);
			$record->recordid = $newId;
			$record->id = $newId;

			$newIds[] = $record;
		}

		$this->plugin->setResponse($newIds);
	}

	/**
	 * POST function unnecessary
	 *
	 * @param   Object  $recordDetails  Single record data
	 * 
	 * @return  STRING  error message
	 * 
	 * @since  3.0
	 **/
	public function saveRec($recordDetails)
	{
		if (!$recordDetails->recordid)
		{
			$item 	= $this->zapp->object->create('Item');

			// Set blog as zoo application
			$item->application_id = 1;

			// Set item type from batch details
			$item->type = $this->batch->params->type;

			$item->created = $this->zapp->date->create()->toSQL();
			$item->publish_up = $this->zapp->date->create()->toSQL();
			$item->created_by = $this->user->id;

			if ($this->zapp->joomla->version->isCompatible('1.6') && $item->access == 0)
			{
				$item->access = $this->zapp->joomla->getDefaultAccess();
			}
		}
		else
		{
			$item	= $this->zapp->table->item->get($recordDetails->recordid);
			$item->modified		= $this->zapp->date->create()->toSQL();
			$item->modified_by	= $this->user->id;
		}

		/* Commented for category column - code to fetch alias of category
		$recCatDetails = explode('/',$recordDetails->category);

		if(count($recCatDetails) == 1)
		{
			$mainCatDets	= (array) $this->zapp->table->category->getByName(1, $recCatDetails[0]);
			$mainCatDet		= reset($mainCatDets);
			$mainCatId		= $mainCatDet->id;
		}
		else
		{
			$parentCatDets = (array) $this->zapp->table->category->getByName(1, $recCatDetails[0]);
			$parentCatDet = reset($parentCatDets);

			$childCatDets	= (array) $this->zapp->table->category->getByName(1, $recCatDetails[1]);

			$mainCatId		= 0;
			foreach ($childCatDets as $childCatK => $childCatV)
			{
				if ($mainCatId = ($childCatV->parent == $parentCatDet->id ? $childCatK : 0))
					break;
			}
		}
		*/

		$item->name		= $recordDetails->name;
		$item->alias	= $recordDetails->alias;
		$item->state	= (trim($recordDetails->state) ? 1 : 0);

		// Set params
		$item->getParams()
			->set('config.primary_category', (int) $recordDetails->category);

		$riProEleData = array();

		foreach ($item->getElements() as $id => $element)
		{
			$type	= $this->decodeElements[$id]->type;
			$opt	= array();

			$stripSlashValue = stripslashes($recordDetails->$id);

			switch ($type)
			{
				case 'text':
					$arr = array();
					$arr[0]['value'] = $stripSlashValue;
					$element->bindData($arr);
				break;
				case 'radio':
					$arr = array();
					$opt['option'][0] = $stripSlashValue;
					$element->bindData($opt);
					break;
				case 'select':
					if ($this->decodeElements[$id]->multiple)
					{
						$impData = array();
						$proarr = array();
						$impData = array_map('trim', explode('|', $stripSlashValue));

						foreach ($impData as $impKey => $impVal)
						{
							$opt['option'][$impKey] = $impVal;
						}
					}
					else
					{
						$proarr = array();
						$opt['option'][0] = $stripSlashValue;
					}

					$element->bindData($opt);

					break;
				case 'textarea':
					$arr = array();
					$arr[0]['value'] = $stripSlashValue;
					$element->bindData($arr);
					break;
				case 'imagepro':
					$img_ele = array();
					$images = explode('|', $stripSlashValue);
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
					$arr['country'][0] = $stripSlashValue;
					$element->bindData($arr);
					break;
				case 'relateditemspro':
					$riProEleData[$id] = $recordDetails->$id;
					$element->bindData();
					break;
				case 'date':
					$arr = array();
					$arr[0]['value'] = (($recordDetails->$id) ? $recordDetails->$id . " 18:30:00" : '');
					$element->bindData($arr);
					break;
			}
		}

		$this->zapp->table->item->save($item);
		$this->zapp->category->saveCategoryItemRelations($item, array((int) $recordDetails->category));

		$riProEleDataFiltered = array_filter($riProEleData);

		if (!empty($riProEleDataFiltered))
		{
			foreach ($riProEleDataFiltered as $fieldK => $fieldV)
			{
				$cFieldV = explode("|", $fieldV);
				$cFieldV = implode('","', $cFieldV);

				$query_field = $this->dbo->getQuery(true);
				$query_field	->select('id')
								->from('#__zoo_item')
								->where('alias in ("' . $cFieldV . '")');
				$this->dbo->setQuery($query_field);
				$ids = $this->dbo->loadColumn();

				if (!empty($ids))
				{
					if ($recordDetails->recordid)
					{
						$query_field = $this->dbo->getQuery(true);
						$query_field	->delete('#__zoo_relateditemsproxref')
										->where('item_id = ' . $recordDetails->recordid . ' AND element_id="' . $fieldK . '"');
						$this->dbo->setQuery($query_field);
						$this->dbo->query();
					}

					foreach ($ids as $iid)
					{
						$idsVal[] = "({$iid}, {$item->id}, '{$fieldK}')";
					}

					$chekcingStr = implode(',', $idsVal);

					$insertQuery	= "INSERT INTO #__zoo_relateditemsproxref (ritem_id, item_id, element_id) VALUES {$chekcingStr}";

					$this->dbo->setQuery($insertQuery);
					$this->dbo->query();
				}
			}
		}

		return $item->id;
	}

	/**
	 * POST function unnecessary
	 *
	 * @param   Object  $recordDetails  Single record data
	 * 
	 * @return  STRING  error message
	 * 
	 * @since  3.0
	 **/
/*	public function saveNew($recordDetails)
	{
		$newItem 	= $this->zapp->object->create('Item');

		Set blog as zoo application
		$newItem->application_id = 1;

		Set item type from batch details
		$newItem->type = $this->batch->params->type;

		Set item name and alias
		$newItem->name	= $recordDetails->name;
		$newItem->alias	= $recordDetails->alias;

		By default publish state
		$newItem->state	= 1;

		$newItem->created = $this->zapp->date->create()->toSQL();
		$newItem->publish_up = $this->zapp->date->create()->toSQL();

		if ($this->zapp->joomla->version->isCompatible('1.6') && $item->access == 0)
		{
			$newItem->access = $this->zapp->joomla->getDefaultAccess();
		}

		$riProEleData = array();

		foreach ($newItem->getElements() as $id => $element)
		{
			$type	= $this->decodeElements[$id]->type;
			$opt	= array();

			switch ($type)
			{
				case 'text':
					$arr = array();
					$arr[0]['value'] = $recordDetails->$id;
					$element->bindData($arr);
				break;
				case 'radio':
					$arr = array();
					$opt['option'][0] = $recordDetails->$id;
					$element->bindData($opt);
					break;
				case 'select':
					if ($this->decodeElements[$id]->multiple)
					{
						$impData = array();
						$proarr = array();
						$impData = array_map('trim', explode('|', $recordDetails->$id));

						foreach ($impData as $impKey => $impVal)
						{
							$opt['option'][$impKey] = $impVal;
						}
					}
					else
					{
						$proarr = array();
						$opt['option'][0] = $recordDetails->$id;
					}

					$element->bindData($opt);

					break;
				case 'textarea':
					$arr = array();
					$arr[0]['value'] = $recordDetails->$id;
					$element->bindData($arr);
					break;
				case 'imagepro':
					$img_ele = array();
					$images = explode('|', $recordDetails->$id);
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
					$arr['country'][0] = $recordDetails->$id;
					$element->bindData($arr);
					break;
				case 'relateditemspro':
					$riProEleData[$id] = $recordDetails->$id;
					$element->bindData();
						break;
				case 'date':
					$arr = array();
					$arr[0]['value'] = $recordDetails->$id . " 18:30:00";
					$element->bindData($arr);
					break;
			}
		}
		try
		{
			// book-rr2-0550128-1|book-rr2-0550513-1|book-rr2-0541804-1|book-rr2-0548755-1

			$this->zapp->table->item->save($newItem);
		}
		catch (Exception $e)
		{
			echo $e;
		}

		return $newItem->id;
	}
*/

	/*
	public function saveNewDuplicate($recordDetails)
	{
		$postRecord = array();

		$this->jinput->set('name', $recordDetails->name);
		$this->jinput->set('alias', $recordDetails->alias);

		$itemCon	= new ItemController($this->zapp);
		$itemCon->save();
		ItemController::save();

		$postRecord['name']			= $recordDetails->name;
		$postRecord['alias']		= $recordDetails->alias;
		$postRecord['state']		= ($recordDetails->state) ? $recordDetails->state : 0;
		$postRecord['searchable']	= 1;

		$postRecord['params']['enable_comments']	= 1;
		$postRecord['params']['primary_category']	= 0;

		$postRecord['params']['metadata']['title'] = '';
		$postRecord['params']['metadata']['description'] = '';
		$postRecord['params']['metadata']['keywords'] = '';
		$postRecord['params']['metadata']['robots'] = '';
		$postRecord['params']['metadata']['author']	= '';


		$postRecord['frontpage']		= 0;
		$postRecord['categories'][0]	=
		$postRecord['type']				= $this->batch->params->type;
		$postRecord['created_by']		= $this->user->id;

		$postRecord['details']['created_by_alias']	= '';
		$postRecord['details']['created']			= '';
		$postRecord['details']['access']			= 1;
		$postRecord['details']['publish_up']		= '';
		$postRecord['details']['publish_down']		= 'Never';

		$postRecord['access']		= 1;
		$postRecord['publish_up']	= '';
		$postRecord['publish_down'] = 'Never';

		echo "<pre>";
		print_r($recordDetails);
		die;
	}
*/
}
