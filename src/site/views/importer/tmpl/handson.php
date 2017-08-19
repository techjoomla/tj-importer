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
$doc->addStyleSheet(JURI::Base() . 'components/com_importer/assets/css/vendor/chosen.css');
$doc->addStyleSheet(JURI::Base() . 'components/com_importer/assets/css/style.css');

$doc->addScript('https://code.jquery.com/jquery-1.12.4.js');

$doc->addScript(JURI::base().'components/com_importer/assets/js/vendor/handsontable.full.js');
//$doc->addScript("https://docs.handsontable.com/pro/bower_components/handsontable-pro/dist/handsontable.full.min.js");
//$doc->addScript("https://docs.handsontable.com/pro/bower_components/numbro/dist/languages.min.js");
$doc->addScript(JURI::base().'components/com_importer/assets/js/vendor/sweetalert-dev.js');
$doc->addScript(JURI::base().'components/com_importer/assets/js/vendor/chosen.js');
$doc->addScript(JURI::base().'components/com_importer/assets/js/importerService.js');
//~ $doc->addScript(JURI::base().'components/com_importer/assets/js/myselectbox.js');
$doc->addScript(JURI::base().'components/com_importer/assets/js/myselectboxcustom.js');
$doc->addScript(JURI::base().'components/com_importer/assets/js/repHandsontable.js');
$doc->addScript(JURI::base().'components/com_importer/assets/js/imageRendererHandson.js');
$doc->addScript(JURI::base().'components/com_importer/assets/js/importerUi.js');
?>

<style>
.overlay {
    height: 0%;
    width: 100%;
    position: fixed;
    z-index: 104;
    top: 0;
    left: 0;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0, 0.8);
    overflow-y: hidden;
    transition: 0.5s;
}

.overlay-content {
    position: relative;
    top: 25%;
    width: 100%;
    text-align: center;
    margin-top: 30px;
}

.overlay a {
    padding: 8px;
    text-decoration: none;
    font-size: 36px;
    color: #818181;
    display: block;
    transition: 0.3s;
}

.overlay a:hover, .overlay a:focus {
    color: #f1f1f1;
}

.overlay .closebtn {
    position: absolute;
    top: 20px;
    right: 45px;
    font-size: 60px;
}

@media screen and (max-height: 450px) {
  .overlay {overflow-y: auto;}
  .overlay a {font-size: 20px}
  .overlay .closebtn {
    font-size: 40px;
    top: 15px;
    right: 35px;
  }
}
</style>



<script>
	jQuery(document).ready(function(){
			var p = jQuery("#example");
			var pos = p.position();
			p.css('height', (window.innerHeight - (pos.top + 40)));
		});


		/* Open */
		function openNav() {
			document.getElementById("myNav").style.height = "100%";
		}

		/* Close */
		function closeNav() {
			document.getElementById("myNav").style.height = "0%";
		}

</script>

<!-- The overlay -->
<div id="myNav" class="overlay">
  <!-- Button to close the overlay navigation -->
  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>

</div>

<!-- Use any element to open/show the overlay navigation menu -->
<span onclick="openNav()">open</span>












<input type="hidden" id="batchId" value=<?php echo $this->batchId; ?>>
<input type="hidden" id="userId" value=<?php echo $this->userId; ?>>
<input type="hidden" id="pfSize" value=<?php echo $this->pfSize; ?>>
<input type="hidden" id="fetchall" value=<?php echo $this->fetchall; ?>>

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
	<div id="importer-buttons-container">

	</div>

	<!-- Div to show handsontable -->
	<div id="example" class="scroll-container"></div>

	<div class="modal fade fade-div" id="repHandsontable" role="dialog" data-backdrop="static" data-keyboard="false" style="display:none;">
		<div class="modal-dialog">
		  <!-- Modal content-->
		  <div class="modal-content">
				<div class="modal-header">
				  <button type="button" class="close" for="step-one-model" onclick="importerUi.dismissModal(this)">&times;</button>
				  <h4 class="modal-title" id="modal-title-2">Chekcing title</h4>
				</div>

				<div class="modal-body" id="repHandsontableBody" style="height:415px;">
					<div id="win-coverrr" class="" style="">
						<div id="win"></div>
					</div>
				</div>

				<div class="modal-footer" id="modal-footer-1">
					<button type="button" class="btn"  id="repHandsontableSave">Save</button>
					<button type="button" class="btn" for="repHandsontable" id="handsontableCLose">Close</button>
				</div>
		  </div>
		</div>
	</div>

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
