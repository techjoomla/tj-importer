<?php
/**
	* @version    SVN: <svn_id>
	* @package    Osians
	* @copyright  Copyright (C) 2005 - 2014. All rights reserved.
	* @license    GNU General Public License version 2 or later; see LICENSE.txt
	*/
require_once JPATH_SITE . DS . 'components' . DS . 'com_osian' . DS . 'common.php';
defined('_JEXEC') or die( 'Restricted access');
jimport('joomla.environment.uri');
jimport('joomla.application.component.controller');

/**
	* This is controller class.
	*
	* @since  1.0.0
	*/
class OsianController extends JController
{
	/**
		* Method to display the view
		*
		* @param   typenotknown  $cachable   .
		*
		* @param   typenotknown  $urlparams  .
		*
		* @access    public
		*
		* @return nothing
		*/
	public function display($cachable = false, $urlparams = false)
	{
		parent::display();
	}

	/**
	 * Added by Amol.
	 * Test function to play with API.
	 *
	 * @access    public
	 *
	 * @return nothing
	 */
	public function mytest()
	{
		$zoo_item_id	= "13217";
		$app 			= App::getInstance('zoo');
		$app->loadHelper(array('zlfw'));

		$item				= $app->table->item->get($zoo_item_id);
		$Related_Categories	= $item->getRelatedCategories();
		$ClassficationInfo	= $Related_Categories[1];
		$CdtInfo 			= $Related_Categories[0];

		echo $CdtInfo->id;
		echo $ClassficationInfo->id;
		die('amol here!!');
	}

	/**
	 * Added by Amol
	 * to get the sef url
	 *
	 * @access    public
	 *
	 * @return nothing
	 */
	public function get_sef_url()
	{
		$var = JRequest::getVar('links');
		$link = JRoute::_($var);
		echo $link;
		Jexit();
	}

			/**
				*Added by Amol
				*This convert simple array to object
				*
				* @param   typenotknown  $array  .
				*
				* @access    public
				*
				* @return  $obj
				*/
	public function array_to_object($array)
	{
		$obj = new stdClass;

		foreach ($array as $k => $v)
		{
			if (is_array($v))
			{
				$obj->{$k} = array_to_object($v);
			}
			else
			{
				$obj->{$k} = $v;
			}
		}

		return $obj;
	}

/**
 * Added by not known
 *
 * @return nothing
 */
	public function getSubId()
	{
		$id = JRequest::getVar('id');
		$itemid = CommonFunctions::getSubmitions($id);
		echo $itemid;
		jexit();
	}

/**
 * Added by not known
 *
 * @return nothing
 */
	public function submission()
	{
		$type = JRequest::getVar('type');
		$itemid = CommonFunctions::getSubmitions($type);
		$apps = JFactory::getApplication();

		$link = 'index.php?option=com_zoo&view=submission&layout=submission&Itemid=' . $itemid;
		$apps->redirect($link);
	}

	/**
	 *  Function to logout
	 *
	 * @return nothing
	 */
	public function logout()
	{
	$app = JFactory::getApplication();
	$user = JFactory::getUser();
	$user_id = $user->get($user->id);
	$app->logout($user_id, array());
	$app->redirect(JURI::base());
	}

	/**
	 *  Added by amol for WNN validation. WNN Must be unique throughtout the site.
	 *
	 * @return nothing
	 */
	public function wnnvalidation()
	{
		$entered_wnn = JRequest::getVar('wnn');
		$edit = JRequest::getVar('edit');
		$db = JFactory::getDBO();
		$readonly = JRequest::getVar('read');
		$key_wnn = JRequest::getVar('key');

		if ($readonly == "true")
		{
			echo 1;
			jexit();
		}

		// Here we can use type as filter to minimize query exection but requirement is not cleared.
		$query = "SELECT elements FROM #__zoo_item WHERE elements LIKE '%$key_wnn%'";
		$db->setQuery($query);
		$result = $db->loadRowList();
		$found = 2;
		$var = 1;

		for ($i = 0; $i <= count($result); $i++)
		{
			$arr_element = json_decode($result[$i][0], 1);

			foreach ($arr_element as $key => $value)
			{
				if ($key == $key_wnn)
				{
					$wnn = $value[0]['value'];

					if ($wnn == $entered_wnn)
					{
						if ($wnn)
						{
							$var++;
							break;
						}
					}
				}
			}
		}

		if ($edit == 1)
		{
			echo $var;
		}
		else
		{
			echo $var;
		}

		jexit();
	}

	/**
	 *  Added by amol for single record iteration
	 *
	 * @return nothing
	 */
	public function single_edit()
	{
		$db = JFactory::getDBO();
		$post = JRequest::get('post');
		$get = JRequest::get('get');

		$zoo_id = $get['zoo_id'];
		$type1 = JRequest::getVar('type');
		$type1 = explode('/', $type1);
		$type = $type1[0];

		$configFile = CommonFunctions::get_config_file_name($type);
		$configFile = CommonFunctions::get_config_type_name($configFile);
		$file = file_get_contents(JPATH_SITE . DS . 'media/zoo/applications/blog/types/' . $configFile . '.config');
		$decode = json_decode($file, true);

		foreach ($decode['elements'] as $k => $ele)
		{
			if ($ele['type'] == "relateditemspro" || $ele['type'] == "imagepro")
			{
				$Pro_Fields[] = $k;
			}
		}

		$img_Key = CommonFunctions::get_img_Key($type);
		$status = $get['status'];
		$operation = $get['op'];

		$state_message_array = CommonFunctions::get_State_Message($operation);
		$subtype = $get['subtype'];
		$totalpage = $get['totalpage'];
		$pitem = $get['pitem'];
		$searchphrase = $get['searchphrase'];
		$Itemid = $get['Itemid'];
		$operation = $get['op'];
		$view = JRequest::getVar('view');

		$zapp = App::getInstance('zoo');
		$s_key = array_search($zoo_id, $post['id']);

		/* We dont want to update record on submitted to save just have to save state of that record.
		 status=all
		 if($status != 3 && $status != -1 && $status != 1 && $status != 0 && $status != 3 && $status != 5)
		 * */

		if ($status == 'all' || $status == 4 || $status == 2 || $operation == "save_revision")
		{
			// Added by amol to Update zoo_category_item table
			$get_CDT_Key = CommonFunctions::get_CDT_Key($type);
			$cat_name = $post['elements'][$get_CDT_Key][$s_key]['option'][0];

			$cat_query = "SELECT id FROM #__zoo_category WHERE name = '$cat_name'";
			$db->setQuery($cat_query);
			$record_cat_id = $db->loadResult();

			$f_cat_data = new stdClass;
			$f_cat_data->category_id = $record_cat_id;
			$f_cat_data->item_id = $zoo_id;

			// Update element column using save api
			$items = $zapp->table->item->all(array('conditions' => 'id =' . $zoo_id));
			$empty_name = $items[$zoo_id]->name;

			foreach ($items as $item)
			{
				foreach ($item->getElements() as $id => $element)
				{
					if (isset($post['elements'][$id]))
					{
						if (!in_array($id, $Pro_Fields))
						{
							$element->bindData($post['elements'][$id][$s_key]);
						}
					}
					else
					{
						$element->bindData();
					}
				}

				$key = CommonFunctions::get_name_key($type);

				if ($type == PEOPLE_CAT_ID)
				{
					// Following keys are  hardcoded. I dont think there is any way to tell zoo that following things are name fields
					$f_name 	= $post['elements'][$key][$s_key][$s_key]['value'];
					$m_name		= $post['elements']['f91f56fc-421a-4d4a-b55a-4bf271d3de29'][$s_key][$s_key]['value'];
					$l_name 	= $post['elements']['4368ec2a-ce56-40e3-a827-bf1e3d7786dd'][$s_key][$s_key]['value'];

					$full_name = $f_name;

					if ($m_name)
					{
						$full_name .= ' ' . $m_name;
					}

					if ($l_name)
					{
						$full_name .= ' ' . $l_name;
					}

					$item->name = $full_name;
				}
				else
				{
					if ($post['elements'][$key][$s_key][$s_key]['value'])
					{
							$item->name = $post['elements'][$key][$s_key][$s_key]['value'];
					}
					else
					{
						$item->name = $empty_name;
					}
				}

				// For mulitple edit here is problem when we on SEF
				$item->alias = rand();
				$zapp->table->item->save($item);
			}
		}
		// Single record save code ends

		$flag = new stdClass;
		$flag->state = $state_message_array[1];

		if ($operation == "edit")
		{
			// Added by amol 32 issue
			$comment_exists_query = "SELECT * FROM #__jacomment_items WHERE contentid = " . $zoo_id;
			$db->setQuery($comment_exists_query);
			$record_exists_in_Comment = $db->loadResult();

			if ($record_exists_in_Comment)
			{
				$flag->state = 5;
			}
			else
			{
				$flag->state = 3;
			}
		}

		if ($operation == "saveid")
		{
			$flag->state = $items[$zoo_id]->state;
		}

		if ($operation == "save" && $status == 4)
		{
			$flag->state = 4;
		}

		if ($operation == "save_revision")
		{
			$flag->state = 7;
		}

		$flag->id = $zoo_id;

		if ($operation != "item_version")
		{
			$db->updateObject('#__zoo_item', $flag, 'id');
		}

		// Copy single item code starts
		if ($operation == "copy" || $operation == "item_version")
		{
			$user = JFactory::getUser();
			$app = App::getInstance('zoo');
			$now  = $app->date->create()->toMySQL();
			$item = $app->table->item->get($zoo_id);
			$categories = $item->getRelatedCategoryIds();

			// Set id to 0, to force new item
			$item->id = 0;

			if ($operation == "item_version")
			{
				$item->state = 7;
				$item->alias = 'version-' . $item->alias;
			}
			else
			{
				$item->state = 2;
				$item->alias = $app->alias->item->getUniqueAlias($zoo_id, 'copy-' . $item->alias);

				// Set copied name
				$item->name .= ' (' . JText::_('Copy') . ')';
			}

			$item->created	   = $item->modified = $now;
			$item->created_by  = $item->modified_by = $user->id;
			$item->hits		   = 0;

			// Copy tags
			$item->setTags($app->table->tag->getItemTags($zoo_id));

			$check_alias_item_id = $app->table->item->all(array('conditions' => 'alias = "' . $item->alias . '"'));

			if (!key($check_alias_item_id))
			{
				$zapp->table->item->save($item);
				$app->category->saveCategoryItemRelations($item->id, $categories);
			}
		}

		if ($operation == "live_revision")
		{
			$app = App::getInstance('zoo');
			$items = $app->table->item->get($zoo_id);
			$items->state = 1;
			$db->updateObject('#__zoo_item', $items, 'id');
			$alias = str_replace('version-', '', $items->alias);
			$items_new = $app->table->item->all(array('conditions' => 'alias = "' . $alias . '"'));
			$source_record_id = key($items_new);
			$item = $app->table->item->get($source_record_id);
			$app->table->item->delete($item);
		}

		// Added by amol
		if ($operation == "delete_revision")
		{
			$app = App::getInstance('zoo');
			$item = $app->table->item->get($zoo_id);
			$app->table->item->delete($item);
		}

		$link = JURI::Base() . "index.php?option=com_osian&view=$view&status=$status&type
		=$type&subtype=$subtype&totalpage=$totalpage&pitem=$pitem&Itemid=$Itemid";

		$app = JFactory::getApplication();

		if (key($check_alias_item_id))
		{
			$replaymessege = "You have already created revisioned record. Please check revisioned records list.";
		}
		else
		{
			$replaymessege = "The action " . $state_message_array[0] . " has been applied to seleted record.";
		}

			$app->enqueueMessage($replaymessege);
			$app->redirect($link);

			// Function single_edit ends
	}

	/**
		* Added by sanyogita
		* save event passed from squeezebox modal dialog
		*
		* @return nothing
		*/
public function modalsave()
{
	$db = JFactory::getDBO();
	$table_name = '#__related_search';
	$data = new stdClass;
	$data->group_name = $word = $_POST['word'];
	$data->group_words = $group = $_POST['group'];

	if ($_POST['id'])
	{
		$query = "UPDATE " . $table_name . " SET group_name = '$word', group_words = '$group' WHERE id=" . $_POST['id'];
		$db->setQuery($query);
		$db->query();
	}
	else
	{
		$db->insertObject($table_name, $data, 'id');
	}

	$apps = JFactory::getApplication();
	$link = 'index.php?option=com_osian&view=addword&tmpl=component';
	$apps->redirect($link, 'Saved successfully');
}

/**
 * Added by not known
 *
 * @return nothing
 */
public function modalsaveclose()
{
	$db = JFactory::getDBO();
	$table_name = '#__related_search';

	$data = new stdClass;
	$data->group_name = $_POST['word'];
	$data->group_words = $_POST['group'];

	if ($_POST['id'])
	{
		$query = "UPDATE " . $table_name . " SET group_name = '$word', group_words = '$group' WHERE id=" . $_POST['id'];

		$db->setQuery($query);
		$db->query();
	}
	else
	{
		$db->insertObject($table_name, $data, 'id');
	}

	$apps = JFactory::getApplication();
	$link = 'index.php?option=com_osian&view=relatedsearch';
	$apps->redirect($link, 'Saved successfully');
}

/**
 * Added by not known
 *
 * @return nothing
 */
public function isthere()
{
	$data = file_get_contents(JPATH_SITE . DS . 'components/com_osian/data.txt');
	$data = explode("\n", $data);
	$db = JFactory::getDBO();
	$type = JRequest::getVar('type');

	foreach ($data as $dat)
	{
		$dat = trim($dat);

		$query = "SELECT id FROM #__zoo_item WHERE name = '{$dat}' AND type = '{$type}'";
		$db->setQuery($query);
		$id = $db->loadResult();

		if ($id)
		{
			echo "exists<br/>";
		}
		elseif (!$id)
		{
			echo $dat . "Does not exists<br/>";
		}
	}
}

/**
 * For batch previews
 *
 * @return nothing
 */
	public function batchaction()
	{
		$db = JFactory::getDBO();
		$apps = JFactory::getApplication();

		// Changed by Mukta to change getint to getvar
		// $query = "SELECT record_id FROM #__batch_item_xref WHERE batch_no =".JRequest::getInt('batch');
		$query = "SELECT record_id FROM #__batch_item_xref WHERE batch_no ='" . JRequest::getVar('batch') . "'";
		$db->setQuery($query);
		$batch_recordss = $db->loadResultArray();
		$batch_records = implode(',', $batch_recordss);

		if (JRequest::getVar('action') == 'deleted')
		{
			$loguser = JFactory::getUser();
			$date = new DateTime;
			$cur_date = date_format($date, 'Y-m-d H:i:s');

			foreach ($batch_recordss as $record)
			{
				/* Delete from zoo
				 Define Zoo app instance.
				 * */
				$app = App::getInstance('zoo');

				// Get the info of $Zoo_Item_Id
				$item = $app->table->item->get($record);

				if ($item->id)
				{
					// Removes the $Zoo_Item_Id record from all respective zoo tables
					$app->table->item->delete($item);
				}

				// Delete record from batch xref
				$query = "DELETE FROM #__batch_item_xref WHERE record_id =" . $record;
				$db->setQuery($query);
				$db->query();
			}
				/*
				* Delete batch entry
				* $query = "DELETE FROM #__batch_info WHERE batch_no =".JRequest::getInt('batch');
				$db->setQuery($query);
				$db->query();
				* */

				echo $query = "UPDATE #__batch_info SET status = 'deleted', publish_user="
				. $loguser->id . " , updated_date='" . $cur_date . "' WHERE batch_no = '"
				. JRequest::getVar('batch') . "'";
				$db->setQuery($query);
				$db->query();
				$redirect_here = 'index.php?option=com_osian&view=batches&sel=batch&batch=' . JRequest::getVar('batch');
		}
		else
		{
			if (JRequest::getVar('action') == 'published')
			{
				$loguser = JFactory::getUser();
				$date = new DateTime;
				$cur_date = date_format($date, 'Y-m-d H:i:s');
				$query = "UPDATE #__zoo_item SET state = 1 WHERE id IN ($batch_records)";
				$db->setQuery($query);
				$db->query();

				$query = "UPDATE #__batch_info SET status = 'published' , publish_user="
				. $loguser->id . " , updated_date='" . $cur_date . "' WHERE batch_no = '"
				. JRequest::getVar('batch') . "'";
				$db->setQuery($query);
				$db->query();

				$redirect_here = 'index.php?option=com_osian&view=batches&sel=batch&batch=' . JRequest::getVar('batch');
			}

			if (JRequest::getVar('action') == 'assigned')
			{
				$query = "UPDATE #__zoo_item SET state = 4, created_by = " . JRequest::getInt('cid') . " WHERE id IN ($batch_records)";
				$db->setQuery($query);
				$db->query();

				$assigned = "assigned to " . JFactory::getUser(JRequest::getInt('cid'))->name;

				$query = "UPDATE #__batch_info SET status = '$assigned' WHERE batch_no = '" . JRequest::getVar('batch') . "'";
				$db->setQuery($query);
				$db->query();

				$redirect_here = 'index.php?option=com_osian&view=batches&sel=batch&batch=' . JRequest::getVar('batch');
			}
		}

		$apps->redirect($redirect_here);

		// $apps->redirect('index.php?option=com_zoo&task=preview&view=preview
		// &layout=preview&category_id=188&batch='.JRequest::getInt('batch'),'Published');
	}

	/**
		* Added by mukta: cron link will be :index.php?option=com_osian&task=generateSEFUrl&type=add-film
		* Type will be changed as per condition.
		* Generate SEF urls for SH404 code starts
		*
		* @return nothing
		*/
	public function generateSEFUrl()
	{
		// Added by amol
		// Array of types of classifications of records links comes up
		$classifications	= array(

								0 => 'antiquities',
								1 => 'architectural-heritage-of-the-indian-sub-continent',
								2 => 'modern-contemporary-fine-arts',
								3 => 'classic-vintage-automobiles',
								4 => 'books-catalogues-publications',
								5 => 'film-publicity-memorabilia',
								6 => 'crafts-games-toys-non-antq',
								7 => 'economic-databases-for-classifications',
								8 => 'events',
								9 => 'photography',
								10 => 'antiquarian-printmaking',
								11 => 'sporting-heritage-including-cricket-football-horse-racing-and-the-olympics',
								// Masterlist types
								12 => 'film-personalities',
								13 => 'masterlist-for-people',
								14 => 'auto-model-masterlist',
								15 => 'add-film',
								16 => 'auctions',
								17 => 'photographers',
								18 => 'institution-master',
								19 => 'auto-personality-master',
								20 => 'masterlist-for-company',
								21 => 'marque-master',
								22 => 'personality-masterlist',
								23 => 'video-masterlist'
							);

		$logfile_path = JPATH_SITE . DS . "components" . DS . "com_osian" . DS . "seflog.txt";
		$old_content = JFile::read($logfile_path);
		$today = 'Start ' . date('Y-m-d H:i:s');
		$this->log[] = JText::sprintf($today);
		$input = JFactory::getApplication()->input;
		$app = JFactory::getApplication();
		$type = $input->get('type');
		$db = JFactory::getDBO();
		$startlimit = $input->get('startlimit');
		$endlimit = $input->get('endlimit');
		$case = $input->get('case');
		$process = $input->get('process');

		// Set the limit to execute no of records in each sho
		$limit = 200;
		$item_id = $input->get('item_id');
		$app = App::getInstance('zoo');

		// Following condition added by Amol to generate SEF URL of single record.

		if ($item_id)
		{
			$ItemInfo = $app->table->item->get($item_id);
			echo $itemLinkCunstruct = $app->route->item($ItemInfo, false);
			echo JRoute::_($itemLinkCunstruct);
		}
		else
		{
			if (!$type)
			{
				if ($old_content)
				{
					JFile::delete($logfile_path);
				}

				$type = 'antiquities';
			}

			if (!$startlimit && !$endlimit && !$process)
			{
				$startlimit = 0;
				$endlimit = $limit;
			}

			// If all records are converted,process=1 in the url, to show message.
			if ($process == 1)
			{
				if (!$case && !$endlimit)
				{
					$key = array_search($type, $classifications);
					$process = 0;
					echo "URLs are converted to SEF successfully!";

					if ($classifications[$key + 1])
					{
						$type = $classifications[$key + 1];
					}
				}
				else
				{
					echo "URLs are converted to SEF successfully!";
					exit();
				}
			}

			$this->log[] = JText::sprintf($startlimit . "\t" . "lmitstart");
			$this->log[] = JText::sprintf($endlimit . "\t" . "endlimit");

			// Get total count
			$query = "SELECT count(id) FROM #__zoo_item WHERE type like '%" . $type . "%'";
			$db->setQuery($query);
			$maxlimit = $db->loadResult();

			$query = "SELECT id FROM #__zoo_item WHERE type like '%" . $type . "%' LIMIT " . $startlimit . "," . $limit;
			$db->setQuery($query);
			$ids = $db->loadObjectList();
			$this->log[] = JText::sprintf($query . "\t" . " query");
			$this->log[] = JText::sprintf(count($ids) . "\t" . " count");

			// Just print the link using zoo api code starts
			if ($ids)
			{
				foreach ($ids as $itemid)
				{
					$ItemInfo 	= $app->table->item->get($itemid->id);

					if ($ItemInfo)
					{
						$itemLinkCunstruct = $app->route->item($ItemInfo, false);
					}

					if ($type == "masterlist-for-people")
					{
						$itemLinkCunstruct .= '&Itemid=407';
						echo JRoute::_($itemLinkCunstruct) . '<br />';
					}
					else
					{
						echo JRoute::_($itemLinkCunstruct) . '<br />';
					}
				}
			}// Print the link using zoo api code ends

			// Increase limit by 1 and take it as a next limitstart. and run the cron for next 400 records.
			$nextlstart = $endlimit + 1;

			if ($maxlimit >= $nextlstart)
			{
				$this->log[] = JText::sprintf($type . "\t" . " type");
				$nextlend = $nextlstart + $limit;
				$this->log[] = JText::sprintf($nextlstart . "\t" . " next lmitstart");
				$this->log[] = JText::sprintf($nextlend . "\t" . "next endlimit");
				$file_log = implode("\n",  $this->log);
				$file_log  = $old_content . "\n" . $file_log;

				// END:write to log file -  BY mukta
				JFile::write($logfile_path, $file_log);

				if ($case)
				{
					$casepar = "&case=1";
				}

				$redirect_url = 'index.php?option=com_osian&task=generateSEFUrl&startlimit='
				. $nextlstart . '&endlimit=' . $nextlend . '&type=' . $type . $casepar;

				// Href using script to avoid execution time error.
				echo "<script> window.location = '{$redirect_url}';</script>";
			}

			// If limit exceeds than record, complete cron action.
			else
			{
				$this->log[] = JText::sprintf("1" . "\t" . " success");
				$msg = 'UrLs are converted to SEF successfully!';

				if ($case)
				{
					$casepar = "&case=1";
				}

				$redirect_url = 'index.php?option=com_osian&task=generateSEFUrl&type='
				. $type . '&process=1' . $casepar;
				echo "<script> window.location = '{$redirect_url}';</script>";
			}
		}
	}

	/**
	 *  Generate SEF urls for SH404 code ends
	 *
	 * @return nothing
	 */
	public function test()
	{
		$app = App::getInstance('zoo');
		$db = JFactory::getDBO();
		$query = "SELECT a.id, a.alias
				FROM  #__zoo_item AS a, #__zoo_category_item AS b
				WHERE a.id = b.item_id
				AND b.category_id = 47";
		$db->setQuery($query);
		$records = $db->loadObjectList();

		$i = 0;

		foreach ($records as $record)
		{
			// $alias = $record->alias.$i;
			echo rtrim($record->alias, $i) . '<br/>';
			$query = "UPDATE #__zoo_item SET alias = '{$alias}' WHERE id = {$record->id}";

			// $db->setQuery($query);
			// $db->query();
		$i++;
		}
	}

	/**
	 * Function added by Mukta for :
	 * copy lead cast and lead cast rest links to masterlists
	 * into a new parameter called 'Actor Featured in Artwork'
	 * run url as : index.php?option=com_osian&task=CopyCast&leadid={lead_Cast_elemnt_id}
	 * &restid={rest lead cast id}&newid={featured actor element id}&type=cine
	 *
	 * @return nothing
	 */
	public function CopyCast()
	{
		$config = JFactory::getConfig();
		$input = JFactory::getApplication()->input;
		$app = JFactory::getApplication();
		$zapp = App::getInstance('zoo');
		$db = JFactory::getDBO();
		$type = $input->get('type');
		$startlimit = $input->get('start');
		$endlimit = $input->get('end');
		$batch = 1;
		$params = $app->getParams('com_osian');
		$lead_cast = $input->get('leadid');
		$rest_cast = $input->get('restid');
		$featured_act = $input->get('newid');

		// Write log
		$logfile_path = JPATH_SITE . DS . "components" . DS . "com_osian" . DS . "copycast.txt";
		$old_content = JFile::read($logfile_path);
		$today = 'Start ' . date('Y-m-d H:i:s');
		$this->log[] = JText::sprintf($today);

		if ($startlimit == '')
		{
			$startlimit = 1;
		}

			$query = "SELECT * FROM #__zoo_category WHERE alias='" . $type . "'";
			$db->setQuery($query);
			$catid = $db->loadObject();

		// Getsubcats
		$query = "SELECT * FROM #__zoo_category WHERE parent=" . $catid->id . " LIMIT " . $startlimit . "," . $batch;
		$db->setQuery($query);
		$subcats = $db->loadObject();

		if (empty($subcats))
		{
			die('Process ends here');
		}

		$this->log[] = JText::sprintf($catid->id . "\t" . " main_catid");
		$this->log[] = JText::sprintf($subcats->id . "\t" . " sub_catid");

		// Get items of subcat
		$itemlist = $zapp->table->item->getByCategory('1', $subcats->id);

		foreach ($itemlist as $items)
		{
			$query = "SELECT * FROM #__zoo_relateditemsproxref WHERE item_id=" . $items->id;
			$db->setQuery($query);
			$ritem_id = $db->loadObjectList();
			$this->log[] = JText::sprintf($items->id . "\t" . " main item id");

			if (empty($ritem_id))
			{
				// Do nothing
			}
			else
			{
				foreach ($ritem_id as $rim)// Get ritemid of one itemid one by one.
				{
					$query = "SELECT * FROM #__zoo_relateditemsproxref WHERE item_id="
					. $rim->ritem_id . " AND element_id IN ('" . $lead_cast . "','" . $rest_cast . "')";
					$db->setQuery($query);
					$subritems = $db->loadObjectList();

					foreach ($subritems as $subr)
					{
							$xrefitem = new stdClass;
							$xrefitem->item_id = $items->id;
							$xrefitem->ritem_id = $subr->ritem_id;
							$xrefitem->element_id = $featured_act;
							$xrefitem->remove = '';
							$xrefitem->params = '';
							$this->log[] = JText::sprintf($items->id . "\t" . " insert item id");
							$this->log[] = JText::sprintf($subr->ritem_id . "\t" . " insert ritem id");

							// Insert the object into the user profile table.
							$result = $db->insertObject('#__zoo_relateditemsproxref', $xrefitem);
					}
				}
			}
		}

		$nextlstart = $startlimit + $batch;
		$this->log[] = JText::sprintf($type . "\t" . " type");
		$this->log[] = JText::sprintf($nextlstart . "\t" . " next lmitstart");
		$file_log = implode("\n",  $this->log);
		$file_log = $old_content . "\n" . $file_log;
		JFile::write($logfile_path, $file_log);
		$redirect_url = 'index.php?option=com_osian&task=CopyCast&leadid='
		. $lead_cast . '&restid=' . $rest_cast . '&newid='
		. $featured_act . '&type=' . $type . '&start=' . $nextlstart;
		echo "<script> window.location = '{$redirect_url}';</script>";
	}

	/**
	 *  Run the actor featured script for limited 100 records.
	 *
	 * @return nothing
	 */
	public function CopyfromMast()
	{
		$config = JFactory::getConfig();
		$input = JFactory::getApplication()->input;
		$app = JFactory::getApplication();
		$zapp = App::getInstance('zoo');
		$db = JFactory::getDBO();
		$type = $input->get('type');
		$startlimit = $input->get('start');
		$endlimit = $input->get('end');
		$batch = 1;
		$params = $app->getParams('com_osian');
		$lead_cast = $input->get('leadid');
		$rest_cast = $input->get('restid');
		$featured_act = $input->get('newid');

		$query = "SELECT count(id) FROM  #__zoo_item WHERE alias >=  'mast-flm-0000001' "
		. "AND alias <=  'mast-flm-0000100'";
		$db->setQuery($query);
		$mast_items_count = $db->loadResult();

		if ($startlimit == '')
		{
			$startlimit = 1;
		}

		// Step1 : get one by one masterlist id
		$query = "SELECT id FROM  #__zoo_item WHERE alias >=  'mast-flm-0000001' "
		. "AND alias <=  'mast-flm-0000100'"
		. " LIMIT " . $startlimit . "," . $batch;
		$db->setQuery($query);
		$mast_item = $db->loadResult();

		// Empty means we have covered all masterlist items within range.
		if (empty($mast_item))
		{
			die('Process ends here');
		}

		// Step2 : select related cine records of that masterlist record.
		// We need to add ritems id of below item_ids
		$query = "SELECT * FROM #__zoo_relateditemsproxref WHERE ritem_id=" . $mast_item;
		$db->setQuery($query);
		$cine_records = $db->loadObjectList();

		// Step3 :get all items of masterlist of lead cast and rest lead cast.
		$query = "SELECT * FROM #__zoo_relateditemsproxref WHERE item_id=" . $mast_item
		. " AND (element_id='" . $lead_cast . "' OR element_id='" . $rest_cast . "')";
		$db->setQuery($query);
		$cast_ids = $db->loadObjectList();

		// Step4 : Connect all leasd casts to featured actor list
		foreach ($cine_records as $circ)
		{
			foreach ($cast_ids as $cids)
			{
				$xrefitem = new stdClass;
				$xrefitem->item_id = $circ->item_id;
				$xrefitem->ritem_id = $cids->ritem_id;
				$xrefitem->element_id = $featured_act;
				$xrefitem->remove = '';
				$xrefitem->params = '';

				// Insert the object into the user profile table.
				$result = $db->insertObject('#__zoo_relateditemsproxref', $xrefitem);
			}
		}

		$nextlstart = $startlimit + $batch;
		$redirect_url = 'index.php?option=com_osian&task=CopyfromMast&leadid='
		. $lead_cast . '&restid=' . $rest_cast . '&newid=' . $featured_act
		. '&type=' . $type . '&start=' . $nextlstart;
		echo "<script> window.location = '{$redirect_url}';</script>";
	}

	/**
	 * Run the script for cdt level.
	 *
	 * @return nothing
	 */
	public function CopyCastforcdt()
	{
		$config = JFactory::getConfig();
		$input = JFactory::getApplication()->input;
		$app = JFactory::getApplication();
		$zapp = App::getInstance('zoo');
		$db = JFactory::getDBO();
		$type = $input->get('type');
		$startlimit = $input->get('start');
		$endlimit = $input->get('end');
		$batch = 1;
		$params = $app->getParams('com_osian');
		$lead_cast = $input->get('leadid');
		$rest_cast = $input->get('restid');
		$featured_act = $input->get('newid');

		// Write log
		if ($startlimit == '')
		{
			$startlimit = 1;
		}

			$query = "SELECT * FROM #__zoo_category WHERE alias='" . $type . "'";
			$db->setQuery($query);
			$catid = $db->loadObject();

		// Getsubcats

		/*$query="SELECT * FROM #__zoo_category WHERE parent=".$catid->id." LIMIT ".$startlimit.",".$batch;
		$db->setQuery($query);
		$subcats=$db->loadObject();
		if(empty($subcats)) {
			die('Process ends here');
		}*/

		// Get items of subcat
		$itemlist = $zapp->table->item->getByCategory('1', $catid->id);

		foreach ($itemlist as $items)
		{
			$query = "SELECT * FROM #__zoo_relateditemsproxref WHERE item_id=" . $items->id;
			$db->setQuery($query);
			$ritem_id = $db->loadObjectList();
			$this->log[]	= JText::sprintf($items->id . "\t" . " main item id");

			if (empty($ritem_id))
			{
				// Do nothing
			}
			else
			{
				// Get ritemid of one itemid one by one.
				foreach ($ritem_id as $rim)
				{
					$query = "SELECT * FROM #__zoo_relateditemsproxref WHERE item_id=" . $rim->ritem_id
					. " AND element_id IN ('" . $lead_cast . "','" . $rest_cast . "')";
					$db->setQuery($query);
					$subritems = $db->loadObjectList();

					foreach ($subritems as $subr)
					{
							$xrefitem = new stdClass;
							$xrefitem->item_id = $items->id;
							$xrefitem->ritem_id = $subr->ritem_id;
							$xrefitem->element_id = $featured_act;
							$xrefitem->remove = '';
							$xrefitem->params = '';
							$this->log[]	= JText::sprintf($items->id . "\t" . " insert item id");
							$this->log[]	= JText::sprintf($subr->ritem_id . "\t" . " insert ritem id");

							// Insert the object into the user profile table.
							$result = $db->insertObject('#__zoo_relateditemsproxref', $xrefitem);
					}
				}
			}
		}

		$nextlstart	=	$startlimit + $batch;
		die('process ends here');
		$redirect_url = 'index.php?option=com_osian&task=CopyCastforcdt&leadid='
		. $lead_cast . '&restid=' . $rest_cast . '&newid=' . $featured_act
		. '&type=' . $type . '&start=' . $nextlstart;
		echo "<script> window.location = '{$redirect_url}';</script>";
	}

	/**
	 * Added by Mukta updated by Amol
	 * Gets the zoo records & save it.
	 * Used to to store Preprogram fields.
	 *
	 * @return nothing
	 */
	public function saveField()
	{
		$jinput			= JFactory::getApplication()->input;
		$zapp			= App::getInstance('zoo');
		$catid			= $jinput->get('catid');
		$count			= $zapp->table->item->getItemCountFromCategory('1', $catid);
		$process		= $jinput->get('process');
		$batch			= 5;
		$start_limit	= $jinput->get('start_limit');
		$end_limit		= $jinput->get('end_limit');

		if (!$start_limit && !$end_limit && !$process)
		{
				$start_limit = 0;
				$end_limit = $batch;
		}

		if ($process == 1)
		{
			die('Completed');
		}

		if ($count < $batch)
		{
			$itemlist = $zapp->table->item->getByCategory('1', $catid, false, null, "", 0, $batch);
		}
		else
		{
			// Break array in batch size
			$itemlist = $zapp->table->item->getByCategory('1', $catid, false, null, "", $start_limit, $batch);
		}

		foreach ($itemlist as $items)
		{
			echo $items->id . "<br/>";
			$item = $zapp->table->item->get($items->id);
			$zapp->table->item->save($item);
		}

		$nextlstart = $end_limit;
		$nextlend = $nextlstart + $batch;

		if ($count > $nextlstart)
		{
			$redirect_url = 'index.php?option=com_osian&task=saveField&catid=' . $catid . '&start_limit=' . $nextlstart . '&end_limit=' . $nextlend;
			echo "<script> window.location = '{$redirect_url}';</script>";
		}
		else
		{
			$redirect_url = JRoute::_('index.php?option=com_osian&task=saveField&catid=' . $catid . 'process=1');
			echo "<script> window.location = '{$redirect_url}';</script>";
		}
	}
	public function testimport()
	{
		$config		   = JFactory::getConfig();
		$this->app	   = JFactory::getApplication();
		$this->dbo	   = JFactory::getDBO();
		$this->zapp	   = App::getInstance('zoo');
		$this->session = JFactory::getSession();
		$this->params  = $this->app->getParams('com_osian');
		$this->jinput  = JFactory::getApplication()->input;
		$user_id = JFactory::getUser();
		$table = $this->zapp->table->item;
		// Create item 
		$item = $this->zapp->object->create('Item');
		$item->alias = 'mast-auc-0000324';
		$item->name = 'Modern & Contemporary South Asian Art';
		$item->type = 'auctions';
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
			->set('config.primary_category', '138');
			/*foreach ($item->getElements() as $id => $element) {
				echo $id."---";
				print_r($element);
				echo "<br/>";
			}die('here');*/
			$arr=array();
			$arr[0]['value']="hello";
			$opt=array();
			$opt['option'][0]="1";
			foreach ($item->getElements() as $id => $element) {
				//text
				if ($id=='7d53ba0c-57a6-4e12-8842-7100713f5157') {
					$element->bindData($arr);
				} 
				else if($id == 'f01eeea1-768c-42e0-9684-48d0ff6a4dcc')
				{
						$element->bindData($opt);
				}else {
					$element->bindData();
				}
			}
			$table->save($item);
			$categories[] = $cid;
		
			$this->zapp->category->saveCategoryItemRelations($item, $categories);
			die('item created');
	}
} // Class ends
