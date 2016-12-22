<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doc = JFactory::getDocument();

$doc->addStyleSheet(JURI::Base() . 'components/com_importer/assets/css/handsontable.full.css');
$doc->addStyleSheet(JURI::Base() . 'components/com_importer/assets/css/sweetalert.css');

//$doc->addScript(JURI::base().'components/com_osian/js/sweetalert.min.js');
$doc->addScript(JURI::base().'components/com_osian/js/jquery.min.js');

$doc->addScript(JURI::base().'components/com_importer/assets/js/handsontable.full.js');
$doc->addScript(JURI::base().'components/com_importer/assets/js/importerService.js');
$doc->addScript(JURI::base().'components/com_importer/assets/js/importerUi.js');
?>

<div class="progress progress-success">
 <div id="pg-bar" class="bar"></div>
</div>

<input type="hidden" id="batchId" value=<?php echo $this->batchId; ?>>

<div id="example"></div>
