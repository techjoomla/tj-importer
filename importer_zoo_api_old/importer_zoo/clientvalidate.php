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
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';
require_once JPATH_SITE . '/plugins/api/importer_zoo/helper.php';

/**
 * Clientcolumns Resource for Importer_zoo Plugin.
 *
 * @since  2.5
 */
class Importer_ZooApiResourceClientvalidate extends ApiResource
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
		// $this->plugin->setResponse("POST method is not supporter, try GET method");
		die("in get funtion");
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
		$this->zapp		= App::getInstance('zoo');
		$columns_array	= $decodeFile = array();
		$jinput			= JFactory::getApplication()->input;
		$this->helper	= new ZooApiHelper;

		$checkRecords 	= (array) json_decode($jinput->get('records', '', 'RAW'));
		$batch 			= json_decode($jinput->get('batchDetails', '', 'STRING'));
		$type			= $batch->params->type;
		$filePath		= JPATH_SITE . '/media/zoo/applications/blog/types/' . $type . '.config';

		if (JFile::exists($filePath))
		{
			$decodeFile		= (array) json_decode(JFile::read($filePath));
			$decodeElements = (array) $decodeFile['elements'];
			$invalidRec		= array();
			$invalidEle		= array();

			// Added alias type forcefully.
			$decodeElements['alias']->type = 'alias';

			$validOptions	= array();

			foreach ($decodeElements as $k => $v)
			{
				if ($v->type == 'radio' || $v->type == 'select')
				{
					$optionsArray = (array) $v->option;

					foreach ($optionsArray as $optionVal)
					{
						$validOptions[$k]['options'][] = $optionVal->value;
					}

					$validOptions[$k]['multiple']	= ($v->multiple) ? 1 : 0;
					$validOptions[$k]['type	']		= $v->type;
				}
			}

			foreach ($checkRecords as $record)
			{
				$record = (array) $record;

				if (!empty(array_filter($record)))
				{
					$tempId = $record['tempId'];
					unset($record['tempId']);

					$invalidEle	= $this->validate($record, $decodeElements, $validOptions);
					$invalidRec[$tempId] = $invalidEle;
				}
			}

			$this->plugin->setResponse($invalidRec);
		}
		else
		{
			die("Type file not found");
		}

		return;
	}

	/**
	 * POST function unnecessary
	 *
	 * @param   Array  $record              A single record from temp table
	 * @param   Array  $decodeElements      Field element details
	 * @param   Array  $validOptionsFields  Field Keys with valid options (Details of only select and radio buttons)
	 * 
	 * @return  STRING  error message
	 * 
	 * @since  3.0
	 **/
	public function validate($record, $decodeElements, $validOptionsFields)
	{
		$invalidFields = null;

		if (!empty(array_filter($record)))
		{
			foreach ($record as $recordKey => $recordData)
			{
				$recordData = stripslashes($recordData);

				if (array_key_exists($recordKey, $decodeElements))
				{
					switch ($decodeElements[$recordKey]->type)
					{
						case 'alias' :
								$correctVal		= $this->zapp->string->sluggify($recordData);

								if ($record['zooid'])
								{
									if ($recordData != $correctVal || $this->zapp->alias->item->checkAliasExists($recordData, $record['zooid']))
									{
										$invalidFields[] = $recordKey;
									}
								}
								else
								{
									if (($recordData != $correctVal) || $this->zapp->alias->item->checkAliasExists($recordData, ''))
									{
										$invalidFields[] = $recordKey;
									}
								}
							break;
						case 'relateditemspro' :

								if (preg_match('/[^a-z0-9|\-]/', trim($recordData))) // '/[^a-z\d]/i' should also work.
								{
									$invalidFields[] = $recordKey;
								}
								else
								{
									$explodeByPipe	= explode("|", trim($recordData));
									$passData	= array_filter($explodeByPipe);

									// $aliasRecords	= $this->zapp->table->item->getByAliases($passData);
									$aliasRecords	= $this->helper->getByAliases($passData);

									if (count($passData) != count($aliasRecords))
									{
										$invalidFields[] = $recordKey;
									}
								}
							break;
						case 'date' :
								if (trim($recordData))
								{
									try
									{
										JHtml::date($recordData, 'Y-m-d H:i:s', 'UTC');
									}
									catch (Exception $e)
									{
										$invalidFields[] = $recordKey;
									}
								}
							break;
						case 'radio' :
								$validOptions	= $validOptionsFields[$recordKey]['options'];

								if (!(in_array($recordData, $validOptions)) && trim($recordData))
								{
									$invalidFields[] = $recordKey;
								}

							break;
						case 'select' :
								$validOptions	= $validOptionsFields[$recordKey]['options'];

								if ($decodeElements[$recordKey]->multiple)
								{
									$explodeByPipe	= explode("|", $recordData);
									$containsSearch = count(array_intersect($explodeByPipe, $validOptions)) == count($explodeByPipe);

									if (!$containsSearch  && trim($recordData))
									{
										$invalidFields[] = $recordKey;
									}
								}
								elseif (!(in_array($recordData, $validOptions)) && trim($recordData))
								{
									$invalidFields[] = $recordKey;
								}
							break;
					}
				}
			}

			if (!isset($record['alias']) || trim($record['alias']) == '')
			{
				$invalidFields[] = 'alias';
			}

			if (!isset($record['name']) || trim($record['name']) == '')
			{
				$invalidFields[] = 'name';
			}

			if (!isset($record['category']) || trim($record['category']) == '')
			{
				$invalidFields[] = 'category';
			}
		}

		return $invalidFields;
	}
}
