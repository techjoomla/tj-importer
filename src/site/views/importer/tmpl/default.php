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


<div>
	<input id='clientApp' type="hidden" value="<?php echo $this->clientApp; ?>">

	
	<div>
	  <button type="button" class="btn demo-btns btn-secondary"  data-toggle="modal" data-target="#myViewModal" >Add Data</button>
	  <button type="button" class="btn demo-btns btn-secondary"  data-toggle="modal" data-target="#myViewModal" >Edit Data</button>
	  <button type="button" class="btn demo-btns btn-secondary"  data-toggle="modal" data-target="#myViewModal" >Load Batch</button>
	  <button type="button" class="btn demo-btns btn-secondary"  data-toggle="modal" >Delete Data</button>
	  <button type="button" class="btn demo-btns btn-secondary"  data-toggle="modal" >Export Data</button>
	  <button type="button" class="btn demo-btns btn-secondary"  data-toggle="modal" >Edit Data</button>
	</div>

	<div class="modal fade" id="myViewModal" role="dialog" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">

		  <!-- Modal content-->
		  <div class="modal-content">
				<div class="modal-header">
				  <button type="button" class="close" data-dismiss="modal">&times;</button>
				  <h4 class="modal-title">This functionality is yet to implement</h4>
				</div>

				<div class="modal-body" >
					<div id="step1">
					</div>
				</div>

				<div class="modal-footer">
				  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
		  </div>

		</div>

	</div>



	

</div>

