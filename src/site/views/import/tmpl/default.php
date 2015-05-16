<?php
$doc = JFactory::getDocument();
$doc->addScript(JURI::base().'components/com_osian/js/jquery.min.js');
$doc->addScript(JURI::base().'components/com_osian/js/import/handsontable.full.min.js');
$doc->addScript(JURI::base().'components/com_osian/js/import/handsontable.js');

$doc->addStyleSheet(JURI::base().'components/com_osian/style/import/handsontable.full.min.css');
$doc->addStyleSheet(JURI::base().'components/com_osian/style/import/handsontable.css');

// If only logged in user can view this.
if(!($this->id)) {
	$apps= JFactory::getApplication();
	$link = 'index.php?option=com_osian&view=logininactive';
	$apps->redirect($link, '');
}
$jinput  = JFactory::getApplication()->input;
$adapter = $jinput->get('adapter');
?>

    <div class="import-cover">


    <div class="import">
	
        
		<form id="bulkimport" name="bulkimport" action="" method="post">
				<div>
					<select id="category" name="category" >
						<?php 
						foreach($this->categories as $catkey=>$catvalue)
						{ ?>
							<option value="<?php echo $catkey; ?>"><?php echo $catvalue; ?></option>
						<?php } ?>
					</select>
				</div>
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
			<input type="hidden" name="option" value="com_osian" />
			<input type="hidden" name="task" value="import.saveBasicDetails" />
			<input type="hidden" name="controller" value="import" />
			<input type="hidden" name="view" value="import" /> 
			<input type="hidden" name="adapter" value="<?php echo $adapter ?>" /> 
		</form>
</div>
</div>

