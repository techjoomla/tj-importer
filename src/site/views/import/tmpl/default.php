<?php
$doc = JFactory::getDocument();
$jinput  = JFactory::getApplication()->input;
$option = $jinput->get('option');
$adapter = $jinput->get('adapter');
//echo $adapter;die;
$doc->addScript(JURI::base().'components/'.$option.'/assets/js/jquery.min.js');
$doc->addScript(JURI::base().'components/'.$option.'/assets/js/handsontable.full.min.js');
$doc->addScript(JURI::base().'components/'.$option.'/assets/js/handsontable.js');

$doc->addStyleSheet(JURI::base().'components/'.$option.'/assets/css/handsontable.full.min.css');
$doc->addStyleSheet(JURI::base().'components/'.$option.'/assets/css/handsontable.css');
$doc->addStyleSheet(JURI::base().'components/'.$option.'/assets/css/bulkimport.css');
//print_r(count($this->categories));
// If only logged in user can view this.
if(!($this->id)) {
	$apps= JFactory::getApplication();
	//$link = 'index.php?option='.$option.'&view=logininactive';
	//$apps->redirect($link, '');
}

?>

    <div class="import-cover">

	<div style="margin-bottom : 8px;">
		<h4><?php echo "Step1 : Add Batch Details" ?></h4>
	</div>
    <div class="import">
	
        
		<form id="bulkimport" name="bulkimport" action="" method="post">
			<?php if ($this->categories && count($this->categories) > 1)
			{ ?>
				<div>
					<select id="category" name="category" style="width:222px!important">
						<?php 
						foreach($this->categories as $catkey=>$catvalue)
						{ ?>
							<option value="<?php echo $catkey; ?>"><?php echo $catvalue; ?></option>
						<?php } ?>
					</select>
				</div>
				<?php } ?>
				<div style="margin-top:10px;margin-bottom: 16px!important;">
					<label><?php echo JText::_( 'COM_IMPORTER_IMPORT_BATCH_NAME') ?></label>
					<input type="text" id="batchname" name="batchname" placeholder="<?php echo JText::_( 'COM_IMPORTER_PLACEHOLDER_BATCHNAME') ?>" value="" />
				</div>
				<div>
					<label><?php echo JText::_( 'COM_IMPORTER_IMPORT_CSV_NAME') ?></label>
					<input type="text" id="filename" name="filename" placeholder="<?php echo JText::_( 'COM_IMPORTER_PLACEHOLDER_FILENAME') ?>" value="" />
				
				</div>
				<?php if ($this->dynamic_columns)
			{ ?>
				<div style="margin-top:10px;margin-bottom: 16px!important;">
					<label><?php echo $this->dynamic_columns['fieldname']; ?></label>
					<input type="text" id="dynamic_field" name="dynamic_field" placeholder="<?php echo $this->dynamic_columns['placeholder'];  ?>" label="<?php echo $this->dynamic_columns['label'];  ?>" value="" />
				</div>
					<?php } ?>
				</div>
			<div class="button-area">
				<input type="submit" value="Submit" id="subform" class="btn btn-default"/>
			</div>
			<input type="hidden" id ="option" name="option" value="<?php echo $option ?>" />
			<input type="hidden" name="task" value="import.saveBasicDetails" />
			<input type="hidden" name="controller" value="import" />
			<input type="hidden" name="view" value="import" /> 
			<input type="hidden" name="adapter" value="<?php echo $adapter ?>" /> 
		</form>
</div>
</div>

