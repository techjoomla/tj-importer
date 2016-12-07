<?php
/**
 * @package	API
 * @version 1.5
 * @author 	Tekdi Technologies
 * @link 	http://www.techjoomla.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgAPIImporter extends ApiPlugin
{
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject = 'api' , $config = array());

		ApiResource::addIncludePath(dirname(__FILE__).'/importer');
	
				
		// Set the login resource to be public
		$this->setResourceAccess('item', 'public','get');
		$this->setResourceAccess('item', 'public','post');
	}
}
