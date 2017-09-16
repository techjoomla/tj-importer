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
require_once JPATH_SITE . '/plugins/api/importer_zoo/helper.php';

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
		$this->helper	= new ZooApiHelper;

		$this->user				= JFactory::getUser();
		$this->records			= json_decode($records);
		$this->batch			= json_decode($batch);
		$this->defaultValues	= (array)json_decode($this->batch->params->defaultVals);
		$newIds					= array();
		$invalid				= array();

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
			$testRecord		= (array) $record;

			unset($testRecord['tempId']);

			// Condition to remove empty records from temp table
			if (empty(array_filter($testRecord)))
			{
				continue;
			}

			// Save function call
			$saveStatus = $this->saveRec($record);

			$record->id = $record->zooid = ($saveStatus['id'] ? $saveStatus['id'] : '');

			$newIds[] = $record;
			$invalid[$record->tempId] = $saveStatus['invalid'];
		}

		$rtrArray = array ('records' => $newIds, 'invalid' => $invalid);

		$this->plugin->setResponse($rtrArray);
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
		if ($this->zapp->alias->item->checkAliasExists($recordDetails->alias, $recordDetails->zooid))
		{
			return array('id' => $recordDetails->zooid, 'invalid' => array('alias'));
		}

		if (!$recordDetails->zooid)
		{
			$item 	= $this->zapp->object->create('Item');

			// Set blog as zoo application
			$item->application_id = 1;

			// Set item type from batch details
			$item->type = $this->batch->params->type;

			$item->created = $this->zapp->date->create()->toSQL();
			$item->modified = $this->zapp->date->create()->toSQL();
			$item->publish_up = $this->zapp->date->create()->toSQL();
			$item->created_by = $this->user->id;

			if ($this->zapp->joomla->version->isCompatible('1.6') && $item->access == 0)
			{
				$item->access = $this->zapp->joomla->getDefaultAccess();
			}
		}
		else
		{
			$item	= $this->zapp->table->item->get($recordDetails->zooid);
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

		if(!empty($this->defaultValues))
		{
			foreach ($this->defaultValues as $defKey=>$defVal)
			{
				$recordDetails->$defKey = $defVal;
			}
		}

		$item->name		= $recordDetails->name;
		$item->alias	= $recordDetails->alias;
		$item->state	= (trim($recordDetails->state) ? 1 : 0);

		// Set params
		$item->getParams()
			->set('config.primary_category', (int) $recordDetails->category);

		$riProEleData = array();
		$imgpresent = 0;

		foreach ($item->getElements() as $id => $element)
		{
			$type	= $this->decodeElements[$id]->type;
			$opt	= array();

			$stripSlashValue = stripslashes($recordDetails->$id);

			switch ($type)
			{
				case 'biography':
					$tempArray = json_decode($stripSlashValue);
					$tempArray2 = array();
					$finArray = array();
					$i	= 0;

					foreach ($tempArray as $tempAr)
					{
						$heading = $tempAr->heading;
						unset($tempAr->heading);
						$tempArray2[$heading][] = (array) $tempAr;
					}

					foreach ($tempArray2 as $tempKey => $tempVal)
					{
						$finArray[$i]['heading'] = $tempKey;
						$finArray[$i]['items'] = $tempVal;

						$i++;
					}

					$element->bindData($finArray);
				break;

				case 'checkbox':
					if (trim($stripSlashValue))
					{
						$impData = array();
						$proarr = array();
						$impData = array_map('trim', explode('|', $stripSlashValue));

						foreach ($impData as $impKey => $impVal)
						{
							$opt['option'][$impKey] = $impVal;
						}

						$opt['count']	= '1';
					}

					$element->bindData($opt);
				break;
				case 'text':
				case 'link':
					$arr = array();
					$arr[0]['value'] = $stripSlashValue;
					$element->bindData($arr);
				break;
				case 'textdate':
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
				case 'date':
					$arr = array();
					$stripSlashArr = explode("|", $stripSlashValue);

					foreach ($stripSlashArr as $impKey => $impVal)
					{
						$arr[0]['value'] = JHtml::date($impVal, 'Y-m-d H:i:s');
					}

					$element->bindData($arr);
					break;
				case 'imagepro':
					$img_ele = array();
					$images = explode('|', $stripSlashValue);
					$i = 0;

					foreach ($images as $img)
					{
						$img = trim($img);

						if (!empty($img))
						{
							$imgpresent = 1;
						}

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
					$riProEleData[$id] = $stripSlashValue;
					$arr = array();

					if (trim($stripSlashValue))
					{
						$idsArray = $this->helper->getByAliases(explode('|', $stripSlashValue), '', true);
						$arr['item'] = $idsArray;
					}

					$element->bindData($arr);

					break;
			}
		}

		try
		{
			$this->zapp->table->item->save($item);
			$this->zapp->category->saveCategoryItemRelations($item, array((int) $recordDetails->category));

			// Save for image resizing
			$this->helper->insertResizeImageRecord($this->batch->id, $item->id, $imgpresent);

			$riProEleDataFiltered = array_filter($riProEleData);

			/*
			if (!empty($riProEleDataFiltered))
			{
				foreach ($riProEleDataFiltered as $fieldK => $fieldV)
				{
					$idsVals	= array();
					$idsVal		= array();
					$cFieldV	= explode("|", $fieldV);
					$cFieldV	= implode('","', $cFieldV);

					$query_field = $this->dbo->getQuery(true);
					$query_field	->select('id')
									->from('#__zoo_item')
									->where('alias in ("' . $cFieldV . '")');
					$this->dbo->setQuery($query_field);
					$ids = $this->dbo->loadColumn();

					if (!empty($ids))
					{
						if ($recordDetails->zooid)
						{
							$query_field = $this->dbo->getQuery(true);
							$query_field	->delete('#__zoo_relateditemsproxref')
											->where('item_id = ' . $recordDetails->zooid . ' AND element_id="' . $fieldK . '"');
							$this->dbo->setQuery($query_field);
							$this->dbo->query();
						}

						foreach ($ids as $iid)
						{
							$idsVals[] = "({$iid}, {$item->id}, '{$fieldK}')";
						}

						$idsVal			= array_unique($idsVals);
						$chekcingStr	= implode(',', $idsVal);

						$insertQuery	= "INSERT INTO #__zoo_relateditemsproxref (ritem_id, item_id, element_id) VALUES {$chekcingStr}";

						$this->dbo->setQuery($insertQuery);
						$this->dbo->query();
					}
				}
			}
			*/

			return array('id' => $item->id, 'invalid' => null);
		}
		catch (Exception $e)
		{
			return array('id' => $item->id, 'invalid' => 1);
		}
	}
}
