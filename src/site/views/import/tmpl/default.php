<?php
$doc = JFactory::getDocument();
$jinput  = JFactory::getApplication()->input;
$option = $jinput->get('option');
$adapter = $jinput->get('adapter');

$doc->addScript(JURI::base().'components/'.$option.'/js/jquery.min.js');
$doc->addScript(JURI::base().'components/'.$option.'/js/import/handsontable.full.min.js');
$doc->addScript(JURI::base().'components/'.$option.'/js/import/handsontable.js');

$doc->addStyleSheet(JURI::base().'components/'.$option.'/style/import/handsontable.full.min.css');
$doc->addStyleSheet(JURI::base().'components/'.$option.'/style/import/handsontable.css');

// If only logged in user can view this.
if(!($this->id)) {
	$apps= JFactory::getApplication();
	$link = 'index.php?option='.$option.'&view=logininactive';
	$apps->redirect($link, '');
}

?>

    <div class="import-cover">

	<div style="margin-bottom : 8px;">
		<h4><?php echo "Step1 : Add Batch Details" ?></h4>
	</div>
    <div class="import">
	
        
		<form id="bulkimport" name="bulkimport" action="" method="post">
			<?php if ($this->categories)
			{ ?>
				<div>
					<select id="category" name="category" >
						<?php 
						foreach($this->categories as $catkey=>$catvalue)
						{ ?>
							<option value="<?php echo $catkey; ?>"><?php echo $catvalue; ?></option>
						<?php } ?>
					</select>
				</div>
				<?php } ?>
				<div style="margin-top:10px;">
					<label><?php echo JText::_( 'IMPORT_BATCH_NAME') ?></label>
					<input type="text" id="batchname" name="batchname" value="" />
				</div>
					<label><?php echo JText::_( 'IMPORT_CSV_NAME') ?></label>
					<input type="text" id="filename" name="filename" value="" />
				<div>
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

