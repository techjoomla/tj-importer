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
class TmtquestionsAdapter
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
		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('quiz.id,quiz.title, count( question.question_id ) AS count')
						->from($this->dbo->quoteName('#__tmt_tests', 'quiz'))
						->join('LEFT', $this->dbo->quoteName('#__tmt_tests_questions', 'question') .
						' ON (' . $this->dbo->quoteName('quiz.id') . ' = ' . $this->dbo->quoteName('question.test_id') . ')')
						->group($this->dbo->quoteName('quiz.id'))
						->having('count =0');
		$this->dbo->setQuery($query_field);
		$data  = $this->dbo->loadObjectList();
		$class_drop_down = array();
		$class_drop_down["select"] = "Select";

		foreach ($data as $val)
		{
			$class_drop_down[$val->id] = $val->title;
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
		$postdata = $this->session->get('postdata');
		$number_of_columns = $postdata->get('dynamic_field', '1', 'STRING');
		$columns_array = array();
		$columns_array['recordid'] = 'recordid';
		$columns_array['type'] = 'Question Type';
		$columns_array['question'] = 'Question';
		$columns_array['description'] = 'Description';
		$columns_array['state'] = 'Published';
		$columns_array['level'] = 'Difficulty Level';
		$columns_array['marks'] = 'Marks';
		$columns_array['catid'] = 'Category ID';

		for ($i = 1; $i <= $number_of_columns; $i++)
		{
			$column_name = 'option' . $i;
			$column_value = 'Option' . $i;
			$columns_array[$column_name] = $column_value;
			$iscorrect_name = 'iscorrect' . $i;
			$iscorrect_value = 'Is correct' . $i;
			$columns_array[$iscorrect_name] = $iscorrect_value;
			$comment_name = 'comment' . $i;
			$comment_value = 'Comment' . $i;
			$columns_array[$comment_name] = $comment_value;
		}

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
		$typearray = array("MC", "TF");
		$invalid_array = array();
		$postdata = $this->session->get('postdata');
		$number_of_columns = $postdata->get('dynamic_field', '1', 'STRING');
		$i = 0;

		// Check Last row if all empty
		$properties = array_filter(get_object_vars($data));

		if (empty($properties))
		{
			return $invalid_array;
		}

		foreach ($data as $datakey => $datavalue)
		{
			if ($datakey == 'question' && $datavalue == '')
			{
				$invalid_array[$i]['element_id']   = $datakey;
				$invalid_array[$i]['value'] = $datavalue;
				$i++;
			}
			elseif ($datakey == 'type' && !in_array(strtoupper($datavalue), $typearray))
			{
				$invalid_array[$i]['element_id']   = $datakey;
				$invalid_array[$i]['value'] = $datavalue;
				$i++;
			}
			elseif ($datakey == 'level' && $datavalue == '')
			{
				$invalid_array[$i]['element_id']   = $datakey;
				$invalid_array[$i]['value'] = $datavalue;
				$i++;
			}
			elseif ($datakey == 'marks' && ($datavalue == '' || $datavalue <= 0))
			{
				$invalid_array[$i]['element_id']   = $datakey;
				$invalid_array[$i]['value'] = $datavalue;
				$i++;
			}
			elseif($datakey == 'catid' && $datavalue == '')
			{
				$invalid_array[$i]['element_id']   = $datakey;
				$invalid_array[$i]['value'] = $datavalue;
				$i++;
			}
		}

		if ($data->type != 'TF')
		{
		$check_options = array();
		$check_correct = array();
		$cnt = count($invalid_array);

		for ($j = 1; $j <= $number_of_columns; $j++)
		{
				$option_name = 'option' . $j;
				$iscorrect_name = 'iscorrect' . $j;
				$check_correct[] = $data->$iscorrect_name;
				$check_options[] = $data->$option_name;
		}

		if (count($check_options) < 2)
		{
			$invalid_array[$cnt + 1]['element_id']   = 'option1';
			$invalid_array[$cnt + 2]['value'] = $data->option1;
		}

		if (!in_array('CORRECT', $check_correct))
		{
			$invalid_array[$cnt + 2]['element_id'] = 'iscorrect1';
			$invalid_array[$cnt + 2]['value'] = $data->iscorrect1;
		}
		}

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
	 * Function to get type of questions.
	 *
	 * @param   string  $type  type pasted in the handsontable.
	 *
	 * @return  return $type_arr[$type] type of text
	 *
	 * @since   1.0.0
	 */
	public function gettype($type)
	{
		$type_arr = array();
		$type_arr['MC'] = 'radio';
		$type_arr['TF'] = 'radio';
		$type_arr['MAT'] = 'checkbox';

		return $type_arr[$type];
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
		$postdata = $this->session->get('postdata');
		$number_of_columns = $postdata->get('dynamic_field', '1', 'STRING');
		$user = JFactory::getUser();
		$value = 1;
		$adapter = ucfirst($postdata->get('adapter', '1', 'STRING'));
		$classname = $adapter . "Adapter";

		if ($imdata->question != '')
		{
			$flag = new stdClass;
			$flag->title  = $imdata->question;
			$flag->description  = $imdata->description;
			$flag->type = $classname::gettype($imdata->type);
			$flag->level = strtolower($imdata->level);
			$flag->ordering = 1;
			$flag->marks = $imdata->marks;
			$flag->state = $imdata->state;
			$flag->ideal_time = 1;
			$flag->category_id = $imdata->catid;
			$flag->created_by = $user->id;
			$flag->	created_on = date('Y-m-d H:i:s');
			$this->dbo->insertObject('#__tmt_questions', $flag, 'id');
			$insert_id = $this->dbo->insertid();
		}
		else
		{
			return 0;
		}

		if ($imdata->type != 'TF')
		{
				for ($i = 1; $i <= $number_of_columns; $i++)
				{
						$option_name = 'option' . $i;
						$iscorrect_name = 'iscorrect' . $i;
						$comment = 'comment' . $i;

						if ($imdata->$option_name && $imdata->$option_name != '')
						{
							if ($imdata->$iscorrect_name == 'CORRECT' || $imdata->$iscorrect_name == 'correct' || $imdata->$iscorrect_name == 1)
							{
								$correct = 1;
								$marks = $imdata->marks;
							}
							elseif ($imdata->$iscorrect_name == 'INCORRECT'  || $imdata->$iscorrect_name == 'incorrect' || $imdata->$iscorrect_name == 0)
							{
								$correct = 0;
								$marks = 0;
							}

							$answers = new stdclass;
							$answers->question_id = $insert_id;
							$answers->answer = $imdata->$option_name;
							$answers->is_correct = $correct;
							$answers->marks = $marks;
							$answers->order = $i;
							$answers->comments = $imdata->$comment;
							$this->dbo->insertObject('#__tmt_answers', $answers, 'id');
						}
				}
		}
		else
		{
			if ($imdata->option1 == '0' || strtolower($imdata->option1) == "false")
			{
				$option1 = "false";
				$option2 = "true";
			}

			if ($imdata->option1 == '1' || strtolower($imdata->option1) == "true")
			{
				$option1 = "true";
				$option2 = "false";
			}

				$answers = new stdclass;
				$answers->question_id = $insert_id;
				$answers->answer = $option1;
				$answers->is_correct = 1;
				$answers->marks = $imdata->marks;
				$answers->order = 1;
				$answers->comments = $imdata->comment1;
				$this->dbo->insertObject('#__tmt_answers', $answers, 'id');

				// For wrong answer
				$answers = new stdclass;
				$answers->question_id = $insert_id;
				$answers->answer = $option2;
				$answers->is_correct = 0;
				$answers->marks = 0;
				$answers->order = 2;
				$answers->comments = $imdata->comment1;
				$this->dbo->insertObject('#__tmt_answers', $answers, 'id');
		}

		$cat_id = $this->session->get('category');

			// Add entry in tmt_test_quiz
			if ($cat_id != '' && $cat_id != 'select')
			{
				$testquiz = new stdclass;
				$testquiz->test_id = $cat_id;
				$testquiz->question_id = $insert_id;
				$testquiz->order = '';
				$this->dbo->insertObject('#__tmt_tests_questions', $testquiz, 'id');
			}

			// Update quiz table too.
			$query_field = $this->dbo->getQuery(true);
			$query_field	->select('total_marks ')
							->from('#__tmt_tests')
							->where('id =' . $cat_id);
			$this->dbo->setQuery($query_field);
			$marks 	  = $this->dbo->loadObject();
			$object = new stdClass;
			$object->id = $cat_id;
			$object->total_marks = $marks->total_marks + $imdata->marks;

			// Update their details in the users table using id as the primary key.
			$result = $this->dbo->updateObject('#__tmt_tests', $object, 'id');

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
		$fields['fieldname'] = 'Maximum number of options';
		$fields['placeholder'] = 'Enter max number of options';
		$fields['label'] = 'Enter maximum number of options you have for the question so that that number of columns will be added';

		return $fields;
	}
}
