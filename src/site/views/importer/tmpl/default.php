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
$doc->addScript('https://code.jquery.com/jquery-1.12.4.js');

$doc->addScript(JURI::base().'components/com_importer/assets/js/handsontable.full.js');
$doc->addScript(JURI::base().'components/com_importer/assets/js/importerService.js');
$doc->addScript(JURI::base().'components/com_importer/assets/js/importerUi.js');

?>


<div>
	<input id='clientApp' type="hidden" value="<?php echo $this->clientApp; ?>">

	
	<div>
	  <button type="button" id="add-data" class="btn demo-btns btn-secondary" onclick="importerUi.showModalFirst(this);" modal-title-set="Select batch details to add records">Add Data</button>
	  <button type="button" id="edit-data" class="btn demo-btns btn-secondary" onclick="importerUi.showModalFirst(this);" modal-title-set="Select batch details to edit records">Edit Data</button>
	  <button type="button" id="load-batch" class="btn demo-btns btn-secondary" data-toggle="modal" data-target="#myViewModal" >Load Batch</button>
	  <button type="button" class="btn demo-btns btn-secondary" data-toggle="modal" >Delete Data</button>
	  <button type="button" class="btn demo-btns btn-secondary" data-toggle="modal" >Export Data</button>
	  <button type="button" class="btn demo-btns btn-secondary" data-toggle="modal" >Edit Data</button>
	</div>

	<div class="modal fade" id="step-one-model" role="dialog" data-backdrop="static" data-keyboard="false" style="display:none;">
		<div class="modal-dialog">
		  <!-- Modal content-->
		  <div class="modal-content">
				<div class="modal-header">
				  <button type="button" class="close" eventFor="step-one-model" onclick="importerUi.dismissModal(this)">&times;</button>
				  <h4 class="modal-title" id="modal-title-1"></h4>
				</div>

				<div class="modal-body" id="step1">
				</div>

				<div class="modal-footer" id="modal-footer-1">
				  <button type="button" class="btn btn-default" eventFor="step-one-model" onclick="importerUi.dismissModal(this)">Close</button>
				</div>
		  </div>
		</div>
	</div>

</div>

<script>
	
</script>
