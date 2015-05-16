<?php
/**
 * @package     Joomla.osian
 * @subpackage  com_osian
 *
 * @copyright   Copyright (C) 2013 - 2014 TWS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.helper');
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';

/**
 * Class to build classification hierarchyh.
 *
 * @package     Joomla.Osian
 * @subpackage  com_osian
 * @since       2.5
 */
class Build_Hierarchy
{
	/**
	 * Function constructor.
	 * 
	 * @param   int  &$subject  subject
	 * 
	 * @param   int  $config    config
	 * 
	 * @since   1.0.0
	 */
	public function __construct(& $subject, $config)
	{
		$config 		= JFactory::getConfig();
		$this->app 		= JFactory::getApplication();
		$this->dbo 		= JFactory::getDBO();
		$this->zapp = App::getInstance('zoo');
		$this->session = JFactory::getSession();
	}

	/**
	 * Function gets config of list of classification and masterlist lists with its config names.
	 * 
	 * @return : config value.
	 *
	 * @since   1.0.0
	 */
	public function getConfig()
	{
		$class_config = JComponentHelper::getParams('com_osian')->get('class_names');

		return $class_config;
	}

	/**
	 * Main function to be called as an object.
	 * Gets config value, processes it and forms array of all classification, cdt and masterlists.
	 * 
	 * @return : array of all classifications, masterlists and cdt data.
	 *
	 * @since   1.0.0
	 */
	public function BuildTree()
	{
		// Read config of com_osian
		$params = $this->getConfig();

		// Build array of classification
		$config_array = $this->build_primary_list($params);

		// Explode by equalto sign
		$config_array = $this->explodebyEqualSign($config_array);

		// Assign other parameter to array
		$final_data = $this->buildOtherParams($config_array);

		return $final_data;
	}

	/** 
	 * Function explodes data taken from osian config, with \n
	 * 
	 * @param   int  $params  osian config value ex. antq=antiquities \n masterlist-for-people=masterlist-for-people
	 * 
	 * @return : array[0]=>'antq=antiquities', array[1]='masterlist-for-people=masterlist-for-people' to BuildTree function
	 *
	 * @since   1.0.0
	 */

	public function build_primary_list($params)
	{
		$explode_params = explode("\n", $params);

		return $explode_params;
	}

	/**
	 * Function gets array formed by exploding using \n.
	 * 
	 * @param   array  $primary_array  array[0]=>'antq=antiquities', array[1]='masterlist-for-people=masterlist-for-people'
	 * and forms the array by exploding with = sign and forming array with alias as keys and config names as values.
	 * 
	 * @return : array([antq]=>[config]='antiqities.config') to BuildTree function
	 *
	 * @since   1.0.0
	 */
	public function explodebyEqualSign($primary_array)
	{
		$build_config_array = array();

		for ($i = 0; $i < count($primary_array); $i++)
		{
			$implode_array_var = explode("=", $primary_array[$i]);

			// Call api of zoo to get category information

			if(is_numeric($implode_array_var[0]))
			{
				$cat_api = $this->getCatInfo($implode_array_var[0]);
				$build_config_array[trim($cat_api->alias)]['id'] = $cat_api->id;
				$build_config_array[trim($cat_api->alias)]['config'] = trim($implode_array_var[1]);
			}
		}

		return $build_config_array;
	}

	/**
	 * Function get category information using zoo api.
	 * 
	 * @param   int  $catid  category id
	 * 
	 * @return : returns all category details
	 *
	 * @since   1.0.0
	 */
	public function getCatInfo($catid)
	{
			$this->zapp->loadHelper(array('zlfw'));
			$catdetail = $this->zapp->table->category->get($catid);

			return $catdetail;
	}

	/**
	 * Function assigns other parameter values to array ex. id, name, description of category.
	 * 
	 * @param   array  $exploded_by_equal_sign  array of classification and config names. (processed after taking from backend)
	 * 
	 * @return : returns newly formatted array with added values to BuildTree function
	 *
	 * @since   1.0.0
	 */
	public function buildOtherParams($exploded_by_equal_sign)
	{
		// Get category details.
		foreach ($exploded_by_equal_sign as $key => $value)
		{
			// Get Category details from paai_zoo_category table
			$cat_details = $this->getCatInfo($value['id']);

			// Form an array
			$exploded_by_equal_sign[$key]['name'] = $cat_details->name;
			$exploded_by_equal_sign[$key]['description'] = $cat_details->description;
			$exploded_by_equal_sign[$key]['parent'] = 0;
			$subcats[$list->alias]['parentid'] = 0;

			// Check if it is masterlist or classification
			$subcatcount = $this->checkType($cat_details->id);

			// Subcatcount = 0 means it is a masterlist
			if ($subcatcount == 0)
			{
				$exploded_by_equal_sign[$key]['display_name'] = $cat_details->alias;
				$exploded_by_equal_sign[$key]['isclassification'] = 0;
				$exploded_by_equal_sign[$key]['ismasterlist'] = 1;
				$exploded_by_equal_sign[$key]['iscdt'] = 0;
			}
			else
			{
				$exploded_by_equal_sign[$key]['display_name'] = strtoupper($cat_details->alias);
				$exploded_by_equal_sign[$key]['isclassification'] = 1;
				$exploded_by_equal_sign[$key]['ismasterlist'] = 0;
				$exploded_by_equal_sign[$key]['iscdt'] = 0;

				// Now get subcats and build array of it.
				$subcats_array = $this->getSubcatstoArray($exploded_by_equal_sign, $cat_details->id);
				$exploded_by_equal_sign = $this->insertSubcatsAfterCat($exploded_by_equal_sign, $key, $subcats_array);
			}
		}

		return $exploded_by_equal_sign;
	}

	/**
	 * Function returns category details when passed alias.
	 * 
	 * @param   int  $cat_alias  category alias ex. antq
	 * 
	 * @return : category information to buildOtherParams function
	 *
	 * @since   1.0.0
	 */
	public function getCatDetails($cat_alias)
	{
		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('*')
						->from('#__zoo_category')
						->where('alias like ' . $this->dbo->quote($cat_alias));
		$this->dbo->setQuery($query_field);
		$cat_info = $this->dbo->loadObject();

		return $cat_info;
	}

	/**
	 * checkType : to check if given cat is masterlist or not. 
	 * checks if category has ant subcats. If no subcats means, it is a masterlist.
	 * 
	 * @param   int  $cat_id  pass category id
	 * 
	 * @return :returns count of subcats.
	 *
	 * @since   1.0.0
	 */
	public function checkType($cat_id)
	{
		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('count(id)')
						->from('#__zoo_category')
						->where('parent = ' . $cat_id);
		$this->dbo->setQuery($query_field);
		$subcat_count = $this->dbo->loadResult();

		return $subcat_count;
	}

	/**
	 * Function gets cdts information when passed parent cat id.
	 * loops through cdt array and assign values
	 * 
	 * @param   array  $exploded_by_equal_sign  Array exploded by equal sign
	 * @param   int    $cat_id                  category id of parent.
	 * 
	 * @return : returns cdts array to explodebyEqualSign function.
	 *
	 * @since   1.0.0
	 */
	public function getSubcatstoArray($exploded_by_equal_sign, $cat_id)
	{
		$query_field = $this->dbo->getQuery(true);
		$query_field	->select('*')
						->from('#__zoo_category')
						->where('parent = ' . $cat_id);
		$this->dbo->setQuery($query_field);
		$subcats_list = $this->dbo->loadObjectList();
		$subcats = array();

		foreach ($subcats_list as $list)
		{
			$cdt_parent_details = $this->getCatInfo($list->parent);
			$subcats[$list->alias]['id'] = $list->id;
			$subcats[$list->alias]['name'] = $list->name;
			$subcats[$list->alias]['description'] = $list->description;
			$subcats[$list->alias]['parent'] = $cdt_parent_details->name;
			$subcats[$list->alias]['parentid'] = $cdt_parent_details->id;
			$subcats[$list->alias]['display_name'] = strtoupper($list->alias);
			$subcats[$list->alias]['config'] = $exploded_by_equal_sign[$cdt_parent_details->alias]['config'];
			$subcats[$list->alias]['isclassification'] = 0;
			$subcats[$list->alias]['ismasterlist'] = 0;
			$subcats[$list->alias]['iscdt'] = 1;
		}

		return $subcats;
	}

	/**
	 * Function to get arrange array such that all cdts come after that classification.
	 * Sliced array till classification ex. antq. merged cdt array after antq and merge remaining classification
	 * array after that.
	 * 
	 * @param   array   $array  main classification array
	 * @param   string  $key    alias of classification (key of array) after which we need to add cdt array
	 * @param   array   $data   data array of cdts
	 * 
	 * @return  array with inserted cdts
	 *
	 * @since   1.0.0
	 */

	public function insertSubcatsAfterCat($array, $key, $data = null)
	{
		$offset = array_search($key, array_keys($array));

		return array_merge(array_slice($array, 0, $offset + 1), (array) $data, array_slice($array, $offset));
	}
}
