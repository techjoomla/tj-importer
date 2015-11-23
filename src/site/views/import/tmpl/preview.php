<?php
$doc = JFactory::getDocument();
$session = JFactory::getSession();
$jinput  = JFactory::getApplication()->input;
$option = $jinput->get('option');
$adapter = $jinput->get('adapter');
$app = JFactory::getApplication();
$params  = $app->getParams($option);
$batchsize = $params->get('import_batch_limit');
$imported_val = $jinput->getInt('imported');//die;

//echo "isint".intval($i);
$doc->addScript(JURI::base().'components/'.$option.'/assets/js/jquery.min.js');
$doc->addScript(JURI::base().'components/'.$option.'/assets/js/handsontable.full.min.js');
$doc->addScript(JURI::base().'components/'.$option.'/assets/js/importdata.js');

$doc->addStyleSheet(JURI::base().'components/'.$option.'/assets/css/handsontable.full.css');
$doc->addStyleSheet(JURI::base().'components/'.$option.'/assets/css/samples.css');
$doc->addStyleSheet(JURI::base().'components/'.$option.'/assets/css/bulkimport.css');

$batchid = $session->get('batch_id');
$total = $this->total; 
$preview_link = $this->previewlink; 
$imported = $this->imported; 
$not_imported = $this->not_imported; 
$link = JURI::Base().'index.php?option='.$option.'&view=import&adapter='.$adapter.'&sel=bulkimport&tmpl=component';
?>
 <div class="import-cover">
<form id="step4" name="step4" action="" method="post">
	<?php if($imported_val == '') 
	{ ?>
		<input type="button" id="importdata" name ="importdata" value="Import"  class="btn btn-default" onclick="importData(0,0);" style="margin-right:10px;margin-bottom-8px;"/>
	<?php } else if($imported_val == '1') { ?>
		<!--<input type="button" id="previewlink" name ="previewlink" value="See preview"  class="btn btn-default" style="margin-right:15px;margin-bottom-8px;"/>-->
		<?php if (isset($preview_link)) { ?>
			<a target="_blank" href ="<?php echo $preview_link ?>" >See Preview</a>
			<?php } ?>
			<div>
				<div>
					<div><h4>Import Report</h4></div>
					<div>Total Records    : <?php echo $total; ?></div>
					<div>Imported Records : <?php echo $imported; ?></div>
					<div>Not Imported Records : <?php echo $not_imported; ?></div>
					<div><h4><a href="<?php echo $link ?>">Import more records</a></h4></div>
				</div>
			</div>
	<?php } ?>
	<!-- code for showing progress bar starts -->
	<div class="cdiv">
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
	<input type="hidden" name="option" id="option" value="<?php echo $option ?>" />
	<input type="hidden" name="task" id="task" value="import.showpreview" />
	<input type="hidden" name="controller" id="controller" value="import" />
	<input type="hidden" name="view" id="view" value="import" />
	<input type="hidden" name="layout" id="layout" value="preview" />
	<input type="hidden" name="csvdata" id="csvdata" value="" />
	<input type="hidden" id= "batchsize" name="batchsize" value="<?php echo $batchsize ?>" />
	<input type="hidden" id = "adapter" name="adapter" value="<?php echo $adapter ?>" /> 
	
</form>
</div>
<script type="text/javascript">
	
jQuery(document).ready(function () {
	loadTable('previewData','');
});
</script>
