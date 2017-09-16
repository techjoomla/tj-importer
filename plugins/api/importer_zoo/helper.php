<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Importer
 *
 * @copyright   Copyright (C) 2016 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die( 'Restricted access' );

// Load ZOO config
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/framework/classes/table.php';
require_once JPATH_SITE . '/components/com_osian/common.php';

/**
 * Importer Plugin.
 *
 * @since  3.0
 */
class ZooApiHelper extends AppTable
{
	/**
	 * Constructor
	 *
	 * @since  3.0
	 */
	public function __construct()
	{
		$this->app			= JFactory::getApplication();
		$this->database		= JFactory::getDBO();
		$this->zapp			= App::getInstance('zoo');
	}

	/**
	 * getByAliases function
	 *
	 * @param   Array   $aliases  A array of aliases
	 * @param   String  $type     The type name
	 *
	 * @return  Object  object of records
	 *
	 * @since  3.0
	 **/
	public function getByAliases($aliases, $type = '', $onlyids = false)
	{
		$aliases = (array) $aliases;

		if (empty($aliases))
		{
			return array();
		}

		$aliasesPipeStr = implode("|", $aliases);

		$sanatizedStr = preg_replace('/[^a-z0-9|\-]/', "-", $aliasesPipeStr);

		$aliasesSanatized = explode("|", $sanatizedStr);

		$strAliases = "'" . implode("','", $aliasesSanatized) . "'";

		$query = $this->database->getQuery(true);

		$query->select('*')
				->from('#__zoo_item')
				->where('alias IN (' . $strAliases . ')')
				->order('FIELD (alias, ' . $strAliases . ' )');

		if ($type && gettype($type) == 'string')
		{
			$query->where('type = "' . $type . '"');
		}
		elseif (!empty($type) && gettype($type) == 'array')
		{
			$typeString	= "'" . implode("','", $type) . "'";
			$query->where('type IN (' . $typeString . ')');
		}

		if ($onlyids)
		{
			$this->database->setQuery($query);
			return $this->database->loadColumn();
		}
		else
		{
			return $this->zapp->table->item->_queryObjectList($query);
		}
	}

	/**
	 * getSuggestions function
	 *
	 * @param   Array   $aliases  A array of aliases
	 * @param   String  $type     The type name
	 *
	 * @return  Object  object of records
	 *
	 * @since  3.0
	 **/
	public function getSuggestions($aliases, $lookTypes = array())
	{
		$aliasesPipeStr = $aliases;

		//$sanatizedStr = preg_replace('/[^a-z0-9|\-]/', "---", $aliasesPipeStr);

		$sanatizedStr = $aliasesPipeStr;

		$query = $this->database->getQuery(true);

		$query->select('id, name, alias')
				->from('#__zoo_item');

		if (!empty ($lookTypes))
		{
			$lookTypesStr = implode('","', $lookTypes);
			$query->where('type IN ("' . $lookTypesStr . '")');
		}

		$query->where('(alias LIKE "%' . $sanatizedStr . '%" OR name LIKE "%' . $sanatizedStr . '%")')
				->order('alias ASC')
				->setLimit('50');

		$this->database->setQuery($query);
		//$suggestions = $this->database->loadAssocList('alias', 'name');
		$suggestions = $this->database->loadRowList();
		//$suggestions = $this->database->loadColumn();


		return $suggestions;
		
		
	}

	/**
	 * Function to insert record to process images resize in resize table.
	 *
	 * @param   Integer  $batch_id  Batch Id.
	 * @param   Integer  $item_id   Record Id.
	 *
	 * @return  return $cat->id updated cat id
	 *
	 * @since   1.0.0
	 */
	public function insertResizeImageRecord($batch_id, $item_id, $imgpresent=1)
	{
		$data			= new stdClass;
		$data->batch_id	= $batch_id;
		$data->item_id	= $item_id;

		$findQuery = $this->database->getQuery(true);
		$findQuery->select("*")->from("#__resize_image_zoo")->where('item_id = ' . $item_id . ' AND batch_id = ' . $batch_id);
		$this->database->setQuery($findQuery);
		$findQueryResult = $this->database->loadResults();

		if (empty($findQueryResult) and $imgpresent==1)
		{
			if (!$this->database->insertObject('#__resize_image_zoo', $data, 'item_id'))
			{
				return $this->database->getErrorMsg() . 'Error occurred while inserting records into resize_image table.';
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/*
		Function: getItemCount
			Method to get COUNT of zoo_itms based on category id and type id.

		Parameters:
			$type_id - Type identifier
			$category_id - category identifier. May be arrya or int value
			$state		- null for all, 0 for unpublished and 1 for published

		Returns:
			Int
	*/
	public function getItemCount($type_id, $category_id, $state = null){

		$query = "SELECT COUNT(a.id)"
			. " FROM #__zoo_item AS a"
			. " LEFT JOIN ".ZOO_TABLE_CATEGORY_ITEM." AS b ON a.id = b.item_id"
			. " WHERE a.application_id = 1"
			. " AND a.type = " . $this->database->Quote($type_id)
			. " AND a.".$this->zapp->user->getDBAccessString($user)

			. ($state !== null ? " AND a.state = " . $this->database->Quote($state) : '' )

            . " AND b.category_id ".(is_array($category_id) ? " IN (".implode(",", $category_id).")" : " = ".(int) $category_id);

		if ((int) $this->zapp->table->item->_queryResult($query) === 0)
		{
			return null;
		}

		return (int) $this->zapp->table->item->_queryResult($query);

	}

	/*
		Function: getByTypeCategory
			Method to get zoo_items based on category id and type id.

		Parameters:
			$type_id - Type identifier
			$category_id - category identifier. May be arrya or int value
			$application_id - 1 for blog
			$state		- null for all, 0 for unpublished and 1 for published
			$user  - user object to check access
			$orderby - 
			$offset  - index no from where to start fetching the records
			$limit	- max limit of items to be fetched.

		Returns:
			Int
	*/
	public function getByTypeCategory($type_id, $category_id, $application_id = false, $state = null, $user = null, $orderby = "", $offset = 0, $limit = 0)
	{
		$date = $this->zapp->date->create();
		$now  = $this->database->Quote($date->toSQL());
		$null = $this->database->Quote($this->database->getNullDate());
 
		$query = "SELECT a.* "
			. " FROM #__zoo_item AS a"
			. " LEFT JOIN ".ZOO_TABLE_CATEGORY_ITEM." AS b ON a.id = b.item_id"
			. " WHERE a.application_id = ".(int) $application_id
			. " AND a.type = ".$this->database->Quote($type_id)
			. " AND a.".$this->zapp->user->getDBAccessString($user)

			. ($state !== null ? " AND a.state = " . $this->database->Quote($state) : '' )

            . " AND b.category_id ".(is_array($category_id) ? " IN (".implode(",", $category_id).")" : " = ".(int) $category_id)
			. " GROUP BY a.id"
			. " ORDER BY ". (!$ignore_order_priority ? "a.priority DESC " : "") . $order
			//. " ORDER BY b.item_id DESC "
			. (($limit ? " LIMIT ".(int) $offset.",".(int) $limit : ""));

		return $this->zapp->table->item->_queryObjectList($query);
	}

	public function getImageInitialPath()
	{
		return CommonFunctions::getBasePathRemoteStorage();
	}

	public function getCategoryIdByType($type_id)
	{
		$typeCatId = array(
			"add-film" => '120',						// Film Tile
			// "antiquarian-printmaking" => '23',   	// PRNT
			// "antiquities" => '15',					// ANTQ
			"article-masterlist" => '824',
			"auto-model-masterlist" => '761',
			"auto-personality-master" => '731',
			// "books-catalogues-publications"=>'17',	// Book
			// "classic-vintage-automobiles" => '717',	// AUTO
			// "crafts-games-toys-non-antq" => '19',	// CRFT
			// "economic-databases-for-classifications" => '20',	// ECON
			"events" => '915',
			"film-festivals-edition" => '914',
			"film-personalities" => '409',
			// "film-publicity-memorabilia" => '18',	// CINE
			"institution-cultural" => '863',
			"institution-master" => '730',
			"marque-master" => '729',
			"masterlist-for-company" => '122',
			"masterlist-for-people" => '121',
			"modern-contemporary-fine-arts" => '16',
			"new-auctions" => '963',
			"new-lots" => '967',
			"periodical-masterlist" => '903',
			"personality-masterlist" => '757',
			"photographers" => '134',
			// "photography" => '22',		// PHTO
			"studio" => '131',
			"videos" => '817',
		);

		return $returnValue = array_key_exists($type_id, $typeCatId) ? $typeCatId[$type_id] : '';
	}
}
