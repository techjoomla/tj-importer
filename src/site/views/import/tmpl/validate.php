<?php
$doc = JFactory::getDocument();
$session = JFactory::getSession();
$jinput  = JFactory::getApplication()->input;
$option = $jinput->get('option');
$adapter = $jinput->get('adapter');
$app = JFactory::getApplication();
$params  = $app->getParams($option);
$batchsize = $params->get('import_batch_limit');
$doc->addScript(JURI::base().'components/'.$option.'/assets/js/jquery.min.js');
$doc->addScript(JURI::base().'components/'.$option.'/assets/js/handsontable.full.min.js');
$doc->addScript(JURI::base().'components/'.$option.'/assets/js/importdata.js');

$doc->addStyleSheet(JURI::base().'components/'.$option.'/assets/css/handsontable.full.css');
$doc->addStyleSheet(JURI::base().'components/'.$option.'/assets/css/samples.css');
$doc->addStyleSheet(JURI::base().'components/'.$option.'/assets/css/bulkimport.css');

$batchid = $session->get('batch_id');

$invalid_data = $this->invalid_data; 
$oinvalid_data = $this->oinvalid_data; 
//print_r($oinvalid_data);die('val');
// It means only columns row is remaining invalid. So go to preview page.
if (count($invalid_data) == 1)
{
	$app = JFactory::getApplication();
	$app->redirect('index.php?option='.$option.'&view=import&layout=preview&adapter='.$adapter.'&sel=bulkimport&tmpl=component');
}
//print_r($invalid_data);die('invalid');
?>
 <div class="import-cover">
<div style="margin-bottom : 8px;margin-left : -33px;">
		<h4><?php echo "Step3 : Correct the values in highlighted cells" ?></h4>
</div>
<form id="step2" name="step2" action="" method="post">
	<div id="errd" name="errd" class="alert alert-error" style="margin:22px auto;">
			<?php echo JText::_( 'VALIDATION_WARNING'); ?>
	</div>
	<div id="addbutton">
		<input type="button" id="validateb" name ="validateb" value="Validate"  class="btn btn-default" onclick="submitForm(0,0,'edit');" style="margin-right:15px;"/>
	</div>
	<!--<input type="button" id="go" name ="go" value="Go"  class="btn btn-default" onclick="validateData(0,0);" style="margin-right:15px;"/>-->
	<!-- code for showing progress bar starts -->
	<div class="cdiv">
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
	<input type="hidden" name="option" id="option" value="<?php echo $option ?>" />
	<input type="hidden" name="task" id="task" value="import.storeCSVData" />
	<input type="hidden" name="controller" id="controller" value="import" />
	<input type="hidden" name="view" id="view" value="import" />
	<input type="hidden" name="layout" id="layout" value="validate" />
	<input type="hidden" name="csvdata" id="csvdata" value="" />
	<input type="hidden" id= "batchsize" name="batchsize" value="<?php echo $batchsize ?>" /> 
	<input type="hidden" id = "adapter" name="adapter" value="<?php echo $adapter ?>" /> 
	
</form>
</div>
<script type="text/javascript">
	
//jQuery(document).ready(function () {
document.addEventListener("DOMContentLoaded", function() {
	loadTable('validateData',<?php echo json_encode($invalid_data); ?>,<?php echo json_encode($oinvalid_data); ?>);
});
</script>
