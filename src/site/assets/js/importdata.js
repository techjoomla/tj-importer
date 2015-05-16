function loadTable(div_id, columnNames)
{
	console.log(columnNames);
	console.log(typeof(columnNames));
	var container = $("#"+div_id);

	if(div_id =='validateData')
	{
		dataval = addFirstRow(columnNames);
		spareRows = 0; 
	}
	else if(div_id == 'previewData')
	{
		dataval = getPreviewData(0,0);
		spareRows = 0; 
	}
	else
	{
		dataval = columnNames;
		spareRows = 1;
	}
	
	$("#"+div_id).handsontable({
	  data: dataval,
	  startRows: 1,
	  startCols: 1,
	  rowHeaders: true,
	  colHeaders: true,
	  minSpareRows: spareRows,
	  contextMenu: true,
	  cells: function(r,c, prop) {
		  var cellProperties = {};
		   if (r === 0) {
			    cellProperties.readOnly = true;
			}
		  if (c===0) cellProperties.readOnly = true;
		  if(div_id == 'validateData')
		{
			 cellProperties.renderer = invalidValueRenderer;
		}
		  
      return cellProperties;
	  }
	});
	
		function invalidValueRenderer(instance, td, row, col, prop, value, cellProperties) {
		if(row==0)
		{
			td.style.fontWeight = 'bold';
		}
		$.each(columnNames, function(index, list) {
//console.log(JSON.parse(list.invalid_columns));

/*console.log("row :"+row);
console.log("col :"+col);
console.log("value :"+value);
console.log("prop :"+prop);*/
	
	});
		Handsontable.renderers.TextRenderer.apply(this, arguments);
	 }
}

function addFirstRow(data) {

	var wnnl = [];

	$.each(data, function(index, wnnlist) {

		var parsedData = JSON.parse(data[index].data);
		data[index].data = parsedData;
		if(data[index].data.recordid != 'recordid')
		{
			data[index].data.recordid = wnnlist.id;    
		}
		var myArrayInJs =wnnlist.data;
		var wnnl1 = [];
		var wnnl2 = {};
		wnnl.push(myArrayInJs);
	});
	 var x=wnnl;
	console.log("x :" + JSON.stringify(data));
	return x;
 }
 
function getPreviewData(start_val, end_val)
{
	var hdiv = 'previewData';

	if(start_val == 0)
	{
		//Initialize progress bar
		document.getElementById("mdiv").style.display = "block";
		$( '#addblur' ).addClass( 'getblur' );
		$( '#percentbar' ).addClass( 'percentbars' );
		//$('#mdiv').css('z-index','999');
		$('#showpercentbar').addClass('showborder');
		$('#perct').html('0%');
		$('#showpercentbar').html('<div style=\"width:0%;background-image:url(components/com_osian/images/pbar-ani.gif)\">&nbsp;</div>');
		//$('#progress').css('width', '0%');
	}
	var adapter = $("#adapter").val();
		$.ajax({
		url:'?option=com_osian&task=import.getPreviewData&start_val='+start_val+'&end_val='+end_val+'&adapter='+adapter,
		//data:{csvdata:csvdata},
		dataType: 'json',
		type: 'POST',
		success: function(data, textStatus, jqXHR) {

			if(data['start']=='complete') {
									document.getElementById("mdiv").style.display = "none";
									$( '#addblur' ).removeClass( 'getblur' );
								}
								else
								{
									// Call function to push data to handsontable
									data_arr = getdatatoPush(data.csvdata, hdiv);
									var percent = Math.round((end_val * 100) / (($('#'+hdiv).handsontable('getData').length)));
									console.log(percent);
									$('#perct').html(percent +"%");
									$('#showpercentbar').html('<div style=\"width:'+percent+'%;background-image:url(components/com_osian/images/pbar-ani.gif)\">&nbsp;</div>');
									$('#progress').css('width', percent+'%');	
									setTimeout(getPreviewData(data['start'],data['end']), 5000);
								}

		},
		error : function(resp){ alert('in error'); }
	});
}
function getdatatoPush(csvdata, divname)
{

	var wnnl = [];
	$.each(csvdata, function(index, wnnlist) {

		var parsedData = JSON.parse(csvdata[index].data);
		csvdata[index].data = parsedData;
		if(csvdata[index].data.recordid != 'recordid')
		{
			csvdata[index].data.recordid = wnnlist.id;    
		}

		var myArrayInJs =wnnlist['data'];
		var wnnl1 = [];
		var wnnl2 = {};

		wnnl.push(myArrayInJs);
		var datacell = $('#'+divname).handsontable('getDataAtCell',0,0);
		console.log("wnnl" + JSON.stringify(wnnl));
		console.log("arrayjs" + JSON.stringify(myArrayInJs));
		// By default table is empty. If empty then load data else push data
		if(!datacell)
		{
			$('#'+divname).handsontable('loadData', wnnl);
			
		}
		else
		{
			$('#'+divname).handsontable('getData').push(myArrayInJs);
			var myArrayInJs = null;
		}
	});
	var x=wnnl;
	$('#'+divname).handsontable('render');

	return x;
}
function submitForm(start_val, end_val, subtype)
{
	if(subtype == 'add')
	{
		var hdiv = 'pastedata';
	}
	if(subtype == 'edit')
	{
		var hdiv = 'validateData';
	}
	$("#csvdata").val(JSON.stringify($('#'+hdiv).handsontable('getData')));
	var csvdata = JSON.stringify($('#'+hdiv).handsontable('getData'));
	var adapter = $("#adapter").val();
	console.log(adapter);
	var batch = 2;
	if(start_val == 0 && end_val == 0)
	{
		
		end_val = batch;
		
		//Initialize progress bar
		document.getElementById("mdiv").style.display = "block";
		$( '#addblur' ).addClass( 'getblur' );
		$( '#percentbar' ).addClass( 'percentbars' );
		//$('#mdiv').css('z-index','999');
		$('#showpercentbar').addClass('showborder');
		$('#perct').html('0%');
		$('#showpercentbar').html('<div style=\"width:0%;background-image:url(components/com_osian/images/pbar-ani.gif)\">&nbsp;</div>');
		//$('#progress').css('width', '0%');
	}
	$.ajax({
		url:'?option=com_osian&task=import.storeCSVData&start_val='+start_val+'&end_val='+end_val+'&type='+subtype+'&adapter='+adapter,
		data:{csvdata:csvdata},
		dataType: 'json',
		type: 'POST',
		success: function(data, textStatus, jqXHR) {

			if(data['start']=='complete') {
									//alert('Export Successful !');
									document.getElementById("mdiv").style.display = "none";
									$( '#addblur' ).removeClass( 'getblur' );
									// alert('success');
									 // Call validation function now.
									 validateData(0,0);
									 //alert('no validate');
								}
								else
								{
									var percent = Math.round((end_val * 100) / (($('#'+hdiv).handsontable('getData').length)));
									console.log(percent);
									$('#perct').html(percent +"%");
									$('#showpercentbar').html('<div style=\"width:'+percent+'%;background-image:url(components/com_osian/images/pbar-ani.gif)\">&nbsp;</div>');
									//$('#progress').css('width', percent+'%');	
									setTimeout(submitForm(data['start'],data['end'], data['subtype']), 5000);
								}
			console.log(data);
			//alert('in success');
		},
		error : function(resp){ alert('in error'); }
	});
	
	//$("#step2").submit();
}

function validateData(start_limit, end_limit)
{
	var adapter = $("#adapter").val();
		if(start_limit == 0 && end_limit == 0)
		{
			//Initialize progress bar
			document.getElementById("mdiv").style.display = "block";
			$( '#addblur' ).addClass( 'getblur' );
			$( '#percentbar' ).addClass( 'percentbars' );
			//$('#mdiv').css('z-index','999');
			$('#showpercentbar').addClass('showborder');
			$('#perct').html('0%');
			$('#showpercentbar').html('<div style=\"width:0%;background-image:url(components/com_osian/images/pbar-ani.gif)\">&nbsp;</div>');
		}
	$.ajax({
		url:'?option=com_osian&task=import.validateData&adapter='+adapter+'&start_limit='+start_limit+'&end_limit='+end_limit,
		dataType: 'json',
		type: 'POST',
		success: function(data, textStatus, jqXHR) {
			if(data['start']=='complete')
			{
				document.getElementById("mdiv").style.display = "none";
				$( '#addblur' ).removeClass( 'getblur' );
				//alert('validation completes');
				
				// Redirect to a view which shows invalid records.
				window.location.href="index.php?option=com_osian&view=import&layout=validate&adapter="+adapter+"&sel=bulkimport";
			}
			else
			{
				//alert('Start :'+data['batch']+' - End :'+ data['count']);
				var percent = Math.round((end_limit * 100) / (data['count']));
				//console.log(percent);
				$('#perct').html(percent +"%");
				$('#showpercentbar').html('<div style=\"width:'+percent+'%;background-image:url(components/com_osian/images/pbar-ani.gif)\">&nbsp;</div>');
				
				setTimeout(validateData(data['start'],data['end']), 5000);
			}
		},
		error : function(resp){ alert('in error'); }
	});
}

function importData(start_limit, end_limit)
{
	var adapter = $("#adapter").val();
	/*	if(start_limit == 0 && end_limit == 0)
		{
			//Initialize progress bar
			document.getElementById("mdiv").style.display = "block";
			$( '#addblur' ).addClass( 'getblur' );
			$( '#percentbar' ).addClass( 'percentbars' );
			//$('#mdiv').css('z-index','999');
			$('#showpercentbar').addClass('showborder');
			$('#perct').html('0%');
			$('#showpercentbar').html('<div style=\"width:0%;background-image:url(components/com_osian/images/pbar-ani.gif)\">&nbsp;</div>');
		}*/
	$.ajax({
		url:'?option=com_osian&task=import.importData&adapter='+adapter+'&start_limit='+start_limit+'&end_limit='+end_limit,
		dataType: 'json',
		type: 'POST',
		success: function(data, textStatus, jqXHR) {
			if(data['start']=='complete')
			{
				//document.getElementById("mdiv").style.display = "none";
				//$( '#addblur' ).removeClass( 'getblur' );
				//alert('validation completes');
				
				// Redirect to a view which shows invalid records.
				//window.location.href="index.php?option=com_osian&view=import&layout=validate&adapter="+adapter+"&sel=bulkimport";
			}
			else
			{
				//alert('Start :'+data['batch']+' - End :'+ data['count']);
				//var percent = Math.round((end_limit * 100) / (data['count']));
				//console.log(percent);
				//$('#perct').html(percent +"%");
				//$('#showpercentbar').html('<div style=\"width:'+percent+'%;background-image:url(components/com_osian/images/pbar-ani.gif)\">&nbsp;</div>');
				
				//setTimeout(importData(data['start'],data['end']), 5000);
			}
		},
		error : function(resp){ alert('in error'); }
	});
}
