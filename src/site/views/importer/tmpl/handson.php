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

$doc->addStyleSheet(JURI::Base() . 'components/com_importer/assets/css/vendor/handsontable.full.css');
$doc->addStyleSheet(JURI::Base() . 'components/com_importer/assets/css/vendor/sweetalert.css');
$doc->addStyleSheet(JURI::Base() . 'components/com_importer/assets/css/style.css');

$doc->addScript('https://code.jquery.com/jquery-1.12.4.js');

$doc->addScript(JURI::base().'components/com_importer/assets/js/vendor/handsontable.full.js');
//$doc->addScript("https://docs.handsontable.com/pro/bower_components/handsontable-pro/dist/handsontable.full.min.js");
//$doc->addScript("https://docs.handsontable.com/pro/bower_components/numbro/dist/languages.min.js");
$doc->addScript(JURI::base().'components/com_importer/assets/js/vendor/sweetalert-dev.js');
$doc->addScript(JURI::base().'components/com_importer/assets/js/importerService.js');
$doc->addScript(JURI::base().'components/com_importer/assets/js/importerUi.js');
?>

<script>
	jQuery(document).ready(function(){
			var p = jQuery("#example");
			var pos = p.position();
			p.css('height', (window.innerHeight - (pos.top + 60)));
		});
</script>

<input type="hidden" id="batchId" value=<?php echo $this->batchId; ?>>
<input type="hidden" id="userId" value=<?php echo $this->userId; ?>>
<input type="hidden" id="pfSize" value=<?php echo $this->pfSize; ?>>

<!-- Div to show progress bar -->
<div class="progress progress-success">
 <div id="pg-bar" class="bar"></div>
</div>

<!-- Div to show progress text -->
<div id="progress-text" class="text-hide">
	<span id="progress-text-span"></span>
	<br/><br/>
	<span id="progress-time-span"></span>
</div>

<div class="fade-div">
	<!-- Div to append control buttons -->
	<div id="importer-buttons-container"></div>

	<!-- Div to show handsontable -->
	<div id="example" class="scroll-container"></div>

	<div class="modal fade" id="batchStatus" role="dialog" data-backdrop="static" data-keyboard="false" style="display:none;">
		<div class="modal-dialog">
		  <!-- Modal content-->
		  <div class="modal-content">
				<div class="modal-header">
				  <button type="button" class="close" for="batchStatus" onclick="importerUi.dismissModal(this)">&times;</button>
				  <h4 class="modal-title" id="batchStatusTitle">Batch Details</h4>
				</div>

				<div class="modal-body" id="batchStatusBody">
					
				</div>

				<div class="modal-footer" id="modal-footer-1">
				  <button type="button" class="btn btn-default" for="batchStatus" onclick="importerUi.dismissModal(this)">Close</button>
				</div>
		  </div>
		</div>
	</div>

</div>
