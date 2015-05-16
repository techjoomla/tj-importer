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

$invalid_data = $this->invalid_data; 
// It means only columns row is remaining invalid. So go to preview page.
if (count($invalid_data) == 1)
{
	$app = JFactory::getApplication();
	$app->redirect('index.php?option='.$option.'&view=import&layout=preview&adapter='.$adapter.'&sel=bulkimport');
}
//print_r($invalid_data);die('invalid');
?>
<form id="step2" name="step2" action="" method="post">
	<input type="button" id="validateb" name ="validateb" value="Validate"  class="btn btn-default" onclick="submitForm(0,0,'edit');" style="margin-right:15px;"/>
	<input type="button" id="go" name ="go" value="Go"  class="btn btn-default" onclick="validateData(0,0);" style="margin-right:15px;"/>
	<!-- code for showing progress bar starts -->
	<div>
		<div id='addblur' style=""></div>
		<div id="mdiv" style="display:none;">
			<div id="percentbar">
						<div id="processtitle"><h4>Processing before validation starts</h4></div>
						<div id="showpercentbar" class="showborder">
							<div id="progress"></div>
						</div>
						<div style="float:right" id="perct"></div>
			<div>
		</div>
	</div>
	</div>
	<!-- code for showing progress bar ends -->
		<div id="validateData" class="handsontable" ></div>
	<input type="hidden" name="option" value="com_osian" />
	<input type="hidden" name="task" id="task" value="import.storeCSVData" />
	<input type="hidden" name="controller" id="controller" value="import" />
	<input type="hidden" name="view" id="view" value="import" />
	<input type="hidden" name="layout" id="layout" value="validate" />
	<input type="hidden" name="csvdata" id="csvdata" value="" />
	<input type="hidden" id = "adapter" name="adapter" value="<?php echo $adapter ?>" /> 
	
</form>
<script type="text/javascript">
	
jQuery(document).ready(function () {
	loadTable('validateData',<?php echo json_encode($invalid_data); ?>);
});
</script>
