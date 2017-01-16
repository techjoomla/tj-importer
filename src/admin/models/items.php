<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * HelloWorldList Model
 *
 * @since  0.0.1
 */
class ImporterModelItems extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'title',
				'state'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*');

		$query->from($db->quoteName('#__importer_items'));

		// Filter: like / search
		$batch_id	= $this->getState('filter.batch_id');
		$validated	= $this->getState('filter.validated');
		$imported	= $this->getState('filter.imported');
		$invalidColumns	= $this->getState('filter.invalid_columns');
		$offset		= $this->getState('filter.offset') ? $this->getState('filter.offset') : 0;
		$limit		= $this->getState('filter.limit');

		if ($batch_id)
		{
			$query->where('batch_id = ' . (int) $batch_id);
		}

		if ($validated)
		{
			$query->where('validated = 1');
		}

		if ($imported)
		{
			$query->where('imported = 1');
		}

		if ($invalidColumns)
		{
			//invalid_columns IS NOT NULL AND invalid_columns
			$query->where('invalid_columns != ""');
		}

		$query->setLimit($limit, $offset);

		/*
		Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published))
		{
			$query->where('state = ' . (int) $published);
		}

		Filter by start & end dates
		if ($up = $this->getState('filter.start_up') && $down = $this->getState('filter.start_down'))
		{
			$query->where("event_start BETWEEN '{$up}' AND '{$down}'");
		}
		elseif ($up = $this->getState('filter.start_up'))
		{
			$query->where("event_start >= '{$up}'");
		}
		elseif ($down = $this->getState('filter.start_down'))
		{
			$query->where("event_start <= '{$down}'");
		}
		*/

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'id');
		$orderDirn 	= $this->state->get('list.direction', 'asc');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}
}
