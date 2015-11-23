function loadTable(div_id, columnNames, invalid_cols)
{
	//console.log(columnNames);
	console.log("invalidcc" +invalid_cols);
	var container = $("#"+div_id);

	if(div_id =='validateData')
	{
		//console.log('valiidate');
		var validatedata =columnNames;
		dataval = addFirstRow(validatedata);
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
	  fixedRowsTop: 0,
	  fixedColumnsLeft: 1,
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

		jQuery.each( invalid_cols, function( i, mydata ) {
			
			if(invalid_cols[i]) {
				var hdata = JSON.parse(invalid_cols[i])


					if(prop == hdata.element_id && row ==hdata.rowno)
					{
						td.style.background = '#CEC';
						td.style.fontWeight = 'bold';
						td.style.color = 'red';
						td.style.border = '1px solid red';
					}
				}
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
	//console.log("x :" + JSON.stringify(data));
	return x;
 }
 
function getPreviewData(start_val, end_val)
{
	var hdiv = 'previewData';
	var component_name = $("#option").val();
	var batchsize = $("#batchsize").val();
	//console.log(batchsize+"batchsize");
	var batch = batchsize;
	if(start_val == 0)
	{
		end_limit = batch;
		//Initialize progress bar
		document.getElementById("mdiv").style.display = "block";
		$( '#addblur' ).addClass( 'getblur' );
		$( '#percentbar' ).addClass( 'percentbars' );
		//$('#mdiv').css('z-index','999');
		$('#showpercentbar').addClass('showborder');
		$('#perct').html('0%');
		$('#showpercentbar').html('<div style=\"width:0%;background-image:url(components/'+component_name+'/images/pbar-ani.gif)\">&nbsp;</div>');
		//$('#progress').css('width', '0%');
	}
	var adapter = $("#adapter").val();
		$.ajax({
		url:'?option='+component_name+'&task=import.getPreviewData&start_val='+start_val+'&end_val='+end_val+'&adapter='+adapter,
		//data:{csvdata:csvdata},
		dataType: 'json',
		type: 'POST',
		success: function(data, textStatus, jqXHR) {

			if(data['start']=='complete') {
									document.getElementById("mdiv").style.display = "none";
									$( '#addblur' ).removeClass( 'getblur' );
									//$('processtitle').html('');
								}
								else
								{
									console.log('in else');
									// Call function to push data to handsontable
									data_arr = getdatatoPush(data.csvdata, hdiv);
									var percent = Math.round((end_val * 100) / (($('#'+hdiv).handsontable('getData').length)));
									if(percent > 100)
									{
										percent = 100;
									}
									//console.log(percent);
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
		console.log(wnnlist);
		var myArrayInJs =wnnlist['data'];
		var wnnl1 = [];
		var wnnl2 = {};

		wnnl.push(myArrayInJs);
		var datacell = $('#'+divname).handsontable('getDataAtCell',0,0);
		//console.log("wnnl" + JSON.stringify(wnnl));
		//console.log("arrayjs" + JSON.stringify(myArrayInJs));
		// By default table is empty. If empty then load data else push data
		if(!datacell)
		{
			$('#'+divname).handsontable('loadData', wnnlist['data']);
			
		}
		else
		{
			$('#'+divname).handsontable('getData').push(wnnlist['data']);
			var myArrayInJs = null;
		}
	});
	var x=wnnl;
	$('#'+divname).handsontable('render');

	return x;
}
function submitForm(start_val, end_val, subtype)
{
	var component_name = $("#option").val();
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
	//console.log("csvdata"+csvdata);
	//console.log("csvdata_slice"+JSON.stringify($('#'+hdiv).handsontable('getData').slice(1, 3)));
	var adapter = $("#adapter").val();
	var batchsize = $("#batchsize").val();
	//console.log(batchsize+"batchsize");
	var batch = batchsize;
	if(start_val == 0 && end_val == 0)
	{
		//start_ind = 1;
		
		end_val = batch;
		
		//Initialize progress bar
		document.getElementById("mdiv").style.display = "block";
		$( '#addblur' ).addClass( 'getblur' );
		$( '#percentbar' ).addClass( 'percentbars' );
		//$('#mdiv').css('z-index','999');
		$('#showpercentbar').addClass('showborder');
		$('#perct').html('0%');
		$('#showpercentbar').html('<div style=\"width:0%;background-image:url(components/'+component_name+'/images/pbar-ani.gif)\">&nbsp;</div>');
		//$('#progress').css('width', '0%');
	}

		var wnnl = [];
		/*var n_cols  =   $('#'+hdiv).handsontable('countCols');
		var $container = $('#'+hdiv);
		var handsontable = $container.data('handsontable');
		var newrowsdata = $container.data('handsontable').getData(start_val,0,end_val,n_cols);
		console.log(newrowsdata));
		var rowsdata =JSON.stringify($('#'+hdiv).handsontable('getData',start_val,0,end_val,n_cols ));*/
		//var rowsdata =$('#'+hdiv).handsontable('getData',start_val,0,end_val,n_cols);
		//,start_val,0,end_val,n_cols
		//console.log("rowsdata" +rowsdata);
		// get rows from handsontable within start and end values
		/*for (var i = start_val; i < end_val; i++) {

		var rowvalue = $('#'+hdiv).handsontable('getSourceDataAtRow',i );
		var rowvaluestring =JSON.stringify($('#'+hdiv).handsontable('getSourceDataAtRow',i ));
		
		//if all rows are complete. ie. values are null then send array as null in order to identify php that records are completed
		if(!rowvaluestring)
		{
			wnnl.push = null;
		}
		else
		{
			wnnl.push(rowvalue);
		}
	}*/
	//console.log(start_val+"-"+end_val);
	// Slice the data to send in a batch
	var datatosend = $('#'+hdiv).handsontable('getData').slice(start_val, end_val);

	var datatosend = JSON.stringify(datatosend);
	//console.log("datatosend"+datatosend);
	$.ajax({
		url:'?option='+component_name+'&task=import.storeCSVData&start_val='+start_val+'&end_val='+end_val+'&type='+subtype+'&adapter='+adapter,
		//data:{csvdata:csvdata},
		data:{csvdata:datatosend},
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
									if(percent > 100)
									{
										percent = 100;
									}
									//console.log(percent);
									$('#perct').html(percent +"%");
									$('#showpercentbar').html('<div style=\"width:'+percent+'%;background-image:url(components/com_osian/images/pbar-ani.gif)\">&nbsp;</div>');
									//$('#progress').css('width', percent+'%');	
									setTimeout(submitForm(data['start'],data['end'], data['subtype']), 5000);
								}
			//console.log(data);
			//alert('in success');
		},
		error : function(resp){ alert('in error'); }
	});
	
	//$("#step2").submit();
}

function validateData(start_limit, end_limit)
{
	var adapter = $("#adapter").val();
	var component_name = $("#option").val();
	var batchsize = $("#batchsize").val();
	//console.log(batchsize+"batchsize");
	var batch = batchsize;
		if(start_limit == 0 && end_limit == 0)
		{
			end_limit = batch;
			//Initialize progress bar
			document.getElementById("mdiv").style.display = "block";
			$( '#addblur' ).addClass( 'getblur' );
			$( '#percentbar' ).addClass( 'percentbars' );
			//$('#mdiv').css('z-index','999');
			$('#processtitle').html("<h4>Validating data...</h4>");
			$('#showpercentbar').addClass('showborder');
			$('#perct').html('0%');
			$('#showpercentbar').html('<div style=\"width:0%;background-image:url(components/'+component_name+'/images/pbar-ani.gif)\">&nbsp;</div>');
		}
	$.ajax({
		url:'?option='+component_name+'&task=import.validateData&adapter='+adapter+'&start_limit='+start_limit+'&end_limit='+end_limit,
		dataType: 'json',
		type: 'POST',
		success: function(data, textStatus, jqXHR) {
			if(data['start']=='complete')
			{
				document.getElementById("mdiv").style.display = "none";
				$( '#addblur' ).removeClass( 'getblur' );
				//alert('validation completes');
				
				// Redirect to a view which shows invalid records.
				window.location.href="index.php?option="+component_name+"&view=import&layout=validate&adapter="+adapter+"&sel=bulkimport";
			}
			else
			{
				//alert('end_limit :'+end_limit+' - count :'+ data['count']);
				var percent = Math.round((end_limit * 100) / (data['count']));
				console.log("percent"+percent);
				if(percent > 100)
				{
					percent = 100;
				}
				$('#perct').html(percent +"%");
				$('#showpercentbar').html('<div style=\"width:'+percent+'%;background-image:url(components/com_osian/images/pbar-ani.gif)\">&nbsp;</div>');
				//alert('end_limit :'+data['start']+' - count :'+ data['end']);
				validateData(data['start'],data['end']);
			}
		},
		error : function(resp){ alert('in error'); }
	});
}

function importData(start_limit, end_limit)
{
	var adapter = $("#adapter").val();
	var component_name = $("#option").val();
	var batchsize = $("#batchsize").val();
	//console.log(batchsize+"batchsize");
	var batch = batchsize;
		if(start_limit == 0 && end_limit == 0)
		{
			end_limit = batch;
			//Initialize progress bar
			document.getElementById("mdiv").style.display = "block";
			$( '#addblur' ).addClass( 'getblur' );
			$( '#percentbar' ).addClass( 'percentbars' );
			//$('#mdiv').css('z-index','999');
			$('#showpercentbar').addClass('showborder');
			$('#perct').html('0%');
			$('#processtitle').html("");
			$('#processtitle').html("<h4>Importing data...</h4>");
			$('#showpercentbar').html('<div style=\"width:0%;background-image:url(components/com_osian/images/pbar-ani.gif)\">&nbsp;</div>');
		}
	$.ajax({
		url:'?option='+component_name+'&task=import.importData&adapter='+adapter+'&start_limit='+start_limit+'&end_limit='+end_limit,
		dataType: 'json',
		type: 'GET',
		success: function(data, textStatus, jqXHR) {
			if(data['start']=='complete')
			{
				document.getElementById("mdiv").style.display = "none";
				$( '#addblur' ).removeClass( 'getblur' );
				alert('Import Successful');
				
				// Redirect to a view which shows invalid records.
				window.location.href="index.php?option=com_osian&view=import&layout=preview&adapter="+adapter+"&sel=bulkimport&imported=1";
			}
			else
			{
				//alert('Start :'+data['batch']+' - End :'+ data['count']);
				var percent = Math.round((end_limit * 100) / (data['count']));
				if(percent > 100)
				{
					percent = 100;
				}
				//var percent = 100;
				//console.log(percent);
				$('#processtitle').html('<h4>Importing data...</h4>')
				$('#perct').html(percent +"%");
				if(percent > 100)
				{
					percent = 100;
				}
				$('#showpercentbar').html('<div style=\"width:'+percent+'%;background-image:url(components/com_osian/images/pbar-ani.gif)\">&nbsp;</div>');
				console.log(data['start']+"--"+data['end']);
				importData(data['start'],data['end']);
			}
		},
		error : function(resp){ alert('in error'); }
	});
}
