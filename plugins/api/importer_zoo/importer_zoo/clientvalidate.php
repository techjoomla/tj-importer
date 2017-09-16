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

		$batchParams			= $batch->params;
		$this->batchFields		= $batchParams->columns;
		$this->defaultValues	= (array)json_decode($batchParams->defaultVals);

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
				if ($v->type == 'radio' || $v->type == 'select' || $v->type == 'checkbox')
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
		$invalidFields	= null;
		$testRecord		= $record;
		unset($testRecord['tempId']);

		if (!empty(array_filter($testRecord)))
		{
			foreach ($record as $recordKey => $recordData)
			{
				$recordData = stripslashes($recordData);

				if (array_key_exists($recordKey, $decodeElements))
				{
					if (!empty(array_filter($this->batchFields)) && !in_array($recordKey, $this->batchFields))
					{
						continue;
					}

					if (!empty(($this->defaultValues)) && array_key_exists($recordKey, $this->defaultValues))
					{
						continue;
					}

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

								$assoTypes = (array) $decodeElements[$recordKey]->application->_chosentypes;

								if (preg_match('/[^a-z0-9|\-]/', trim($recordData))) // '/[^a-z\d]/i' should also work.
								{
									$invalidFields[] = $recordKey;
								}
								else
								{
									$explodeByPipe	= explode("|", trim($recordData));
									$passData	= array_filter($explodeByPipe);

									// $aliasRecords	= $this->zapp->table->item->getByAliases($passData);
									$aliasRecords	= $this->helper->getByAliases($passData, $assoTypes);

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
						case 'textdate' :
								if (trim($recordData))
								{
									if ($this->validateTextDate($recordData))
									{
										$invalidFields[] = $recordKey;
									}
								}
							break;
						case 'biography' :
								if (trim($recordData))
								{
									$recordDataArr	= json_decode($recordData);
									$invalidBioKeys	= array();

									foreach ($recordDataArr as $bioKy => $bioData)
									{
										if (trim($bioData->stdStart) && $this->validateTextDate($bioData->stdStart))
										{
											$invalidBioKeys[$bioKy][] = 'stdStart';
										}

										if (trim($bioData->stdEnd) && $this->validateTextDate($bioData->stdEnd))
										{
											$invalidBioKeys[$bioKy][] = 'stdEnd';
										}
									}

									if (!empty($invalidBioKeys))
									{
										$invalidFields[] = array($recordKey => $invalidBioKeys);
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
						case 'checkbox' :
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

			if ((!isset($record['category']) || trim($record['category']) == '') && !array_key_exists('category', $this->defaultValues))
			{
				if (empty(array_filter($this->batchFields)) || (!empty(array_filter($this->batchFields)) && in_array('category', $this->batchFields)))
				{
					$invalidFields[] = 'category';
				}
			}
		}

		return $invalidFields;
	}

	/**
	 * validateTextDate function
	 *
	 * @param   String  $recordData  Date string
	 * 
	 * @return  STRING  error message
	 * 
	 * @since  3.0
	 **/
	private function validateTextDate($recordData)
	{
		$invalidField = false;

		try
		{
			// Trim Spaces
			$recordDataTrim		= trim($recordData);

			$bcDate				= ($recordDataTrim[0] == '-');

			// Remove minus sign
			$recordDataFinal	= (($recordDataTrim[0] != '-') ? $recordDataTrim : ltrim($recordDataTrim, "-"));

			// Separate out date and time string in an array
			$dateTime		= explode(" ", $recordDataFinal);
			$date			= trim($dateTime[0]);
			$time			= trim($dateTime[1]);

			if (preg_match('/[^0-9\-]/', $date) || count($dateTime) > 2 || preg_match('/[^0-9\:]/', $time))
			{
				$invalidField = true;
			}

			// Separate out date and time parameters in an array
			$dateArray		= array();
			$dateArray		= explode("-", $date);
			$timeArray		= array();
			$timeArray		= explode(":", $time);

			if (($bcDate && !empty(array_filter($timeArray))) || ($bcDate && (count($dateArray) > 1)))
			{
				$invalidField = true;
			}

			// Check if minutes are empty. If yes, append 00 to string
			if (!empty(array_filter($timeArray)) && trim($timeArray[1]) == '')
			{
				$recordData		= trim($recordData) . ":00";
				$timeArray[1] = '00';
			}

			JHtml::date($recordData, 'Y-m-d H:i:s', 'UTC');

			// Check time params
			if (((int) $timeArray[0] > 23) || ((int) $timeArray[1] > 59) || ((int) $timeArray[2] > 59))
			{
				$invalidField = true;
			}
			elseif ((strlen($dateArray[0]) < 4) || ((count(array_filter($dateArray)) < 3) && !empty(array_filter($timeArray))))
			{
				$invalidField = true;
			}
			elseif (($dateArray[0] == '0000') || ((int) $dateArray[1] < 1 && isset($dateArray[1])))
			{
				// Check year and month
				$invalidField = true;
			}
			elseif (isset($dateArray[1]) && isset($dateArray[2]))
			{
				// Chekcing days
				$dayThirty = array(4, 6, 9, 11);

				if ((int) $dateArray[2] < 1)
				{
					$invalidField = true;
				}
				elseif (in_array((int) $dateArray[1], $dayThirty) && ((int) $dateArray[2] > 30))
				{
					$invalidField = true;
				}
				elseif ((int) $dateArray[1] == 2)
				{
					$year	= (int) $dateArray[0];

					if ((int) $dateArray[2] > 29)
					{
						$invalidField = true;
					}
					elseif (!((0 == $year % 4) && (0 != $year % 100) || (0 == $year % 400)) && ((int) $dateArray[2] > 28))
					{
						$invalidField = true;
					}
				}
			}
		}
		catch (Exception $e)
		{
			$invalidField = true;
		}

		return $invalidField;
	}
}
