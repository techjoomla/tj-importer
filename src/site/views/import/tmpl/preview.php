<?php
$doc = JFactory::getDocument();
$session = JFactory::getSession();
$doc->addScript(JURI::base()."components/com_osian/js/bulkedit/jquery.min.js");
$doc->addScript(JURI::base()."components/com_osian/js/import/handsontable.full.min.js");
//$doc->addScript(JURI::base()."components/com_osian/js/import/handsontable.js");
$doc->addScript(JURI::base()."components/com_osian/js/import/importdata.js");

$doc->addStyleSheet(JURI::base().'components/com_osian/style/import/handsontable.full.css');
$doc->addStyleSheet(JURI::base().'components/com_osian/style/import/samples.css');
$doc->addStyleSheet(JURI::base().'components/com_osian/style/import/bulkimport.css');
$jinput  = JFactory::getApplication()->input;
$adapter = $jinput->get('adapter');
$option = $jinput->get('option');
$batchid = $session->get('batch_id');

?>
<form id="step4" name="step4" action="" method="post">
	<input type="button" id="importdata" name ="importdata" value="Import"  class="btn btn-default" onclick="importData(0,0);" style="margin-right:15px;"/>
	<!-- code for showing progress bar starts -->
	<div>
		<div id='addblur' style=""></div>
		<div id="mdiv" style="display:none;">
			<div id="percentbar">
						<div id="processtitle"><h4>Building preview</h4></div>
						<div id="showpercentbar" class="showborder">
							<div id="progress"></div>
						</div>
						<div style="float:right" id="perct"></div>
			<div>
		</div>
	</div>
	</div>
	<!-- code for showing progress bar ends -->
		<div id="previewData" class="handsontable" ></div>
	<input type="hidden" name="option" value="com_osian" />
	<input type="hidden" name="task" id="task" value="import.showpreview" />
	<input type="hidden" name="controller" id="controller" value="import" />
	<input type="hidden" name="view" id="view" value="import" />
	<input type="hidden" name="layout" id="layout" value="preview" />
	<input type="hidden" name="csvdata" id="csvdata" value="" />
	<input type="hidden" id = "adapter" name="adapter" value="<?php echo $adapter ?>" /> 
	
</form>
<script type="text/javascript">
	
jQuery(document).ready(function () {
	loadTable('previewData','');
});
</script>
