
var importerService = {

	clientApp : '',
	urlTypeList : "index.php?option=com_api&app=importer_" + this.clientApp + "&resource=clienttypes&format=raw",
	urlColumnList : '',
	urlClientRecords : '',
	urlTempReocrds : '',

	getTypeList : function(){
			var	clientApp	= this.clientApp;
			var clientTypes	= jQuery.ajax({
						type: "GET",
						url: "index.php?option=com_api&app=importer_" + this.clientApp + "&resource=clienttypes&format=raw"
					});
			return clientTypes;
		},

	getFieldList : function (typeSelected, fieldSelected = ''){
			var	clientApp	=  this.clientApp ;

			var clientColumns = jQuery.ajax({
					type: "GET",
					url: "index.php?option=com_api&app=importer_" + this.clientApp + "&resource=clientcolumns&format=raw&type=" + typeSelected,
					data : {fields : fieldSelected}
				});
			
			return clientColumns;
		},

	getRecordsList : function(type, columns, ids)
		{
			var	clientApp	=  this.clientApp ;

			var clientRecords = jQuery.ajax({
					type: "GET",
					url: "index.php?option=com_api&app=importer_" + clientApp + "&resource=clientrecords&format=raw",
					data : {type: type, fields : columns, ids : ids}
				});

			return clientRecords;
		},

	getRecordsTemp : function(batchId)
		{
			let tempRecords = jQuery.ajax({
					type: "GET",
					url: "index.php?option=com_api&app=importer&resource=item&format=raw",
					data : {batch_id: batchId}
				});

			return tempRecords;
		},

	saveBatch : function(batchParams, recordsSelected){
			var	clientApp	= this.clientApp;
			var batch_name = batchParams.batchName;
			var client	= clientApp;
			var import_status = 0;
			var created_date = '';
			var updated_date = '';
			var import_user = 1234;	
			var params		= batchParams;
			var start_id	= '';

			var saveResult = jQuery.post( 
								"index.php?option=com_api&app=importer&resource=batch&format=raw", 
								{ 'JForm': {
											batch_name : batch_name, 
											client : client, 
											params : JSON.stringify(params),
											start_id : recordsSelected
										}
								}
							);

			console.log(saveResult);
			return saveResult;
		},

	updateBatch : function(batchDetails){

			var batchDetails = jQuery.ajax({
					type: "POST",
					url: "index.php?option=com_api&app=importer&resource=batch&format=raw",
					data: {JForm:batchDetails}
				});
			
			return batchDetails;
		},

	getBatch : function(batchId){
		
			var batchDetails = jQuery.ajax({
					type: "GET",
					url: "index.php?option=com_api&app=importer&resource=batch&format=raw",
					data: {id:batchId}
				});
			
			return batchDetails;
		},

	saveTempRecords : function(records, batchDetails){

			var batchDetails = jQuery.ajax({
					type: "POST",
					url: "index.php?option=com_api&app=importer&resource=item&format=raw",
					data: {records : JSON.stringify(records), batchDetails : batchDetails}
				});

			return batchDetails;
		}
}
	
	
	
	
	
	
	
	
	
	
	
	
/*jQuery(document).ready(function(){

	var mainDiv = jQuery("#step1");
	var clientApp = jQuery("#clientApp").val();

	var getFieldList = function (){
				var typeSelected = jQuery("option:selected", this).val();
				jQuery.ajax({
						url: "/toolsite/index.php?option=com_api&app=importer_" + clientApp + "&resource=clientcolumns&format=raw&type=" + typeSelected,
						success: function(resultFields)
						{
							var columnDropDown = createDropDownList('columnList', 'columnList', resultFields, true);
							jQuery(".columnList").remove();
							jQuery("#getrecords").remove();

							mainDiv.append(columnDropDown);
							mainDiv.append("<textarea id='getrecords'></textarea>");
						}
				});
			};

	var getTypeList = function (clientApp){
						jQuery.ajax({
							url: "/toolsite/index.php?option=com_api&app=importer_" + clientApp + "&resource=clienttypes&format=raw",
							success: function(result)
							{
								var typeDropDown = createDropDownList('typeList', 'typeList', result, false);
								typeDropDown.on('change', getFieldList);

								typeDropDown.on('change', function(){
									var typeSelected = jQuery("option:selected", this).val();
									jQuery.ajax({
											url: "/toolsite/index.php?option=com_api&app=importer_" + clientApp + "&resource=clientcolumns&format=raw&type=" + typeSelected,
											success: function(resultFields)
											{
												var columnDropDown = createDropDownList('columnList', 'columnList', resultFields, true);
												jQuery(".columnList").remove();
												jQuery("#getrecords").remove();

												mainDiv.append(columnDropDown);
												mainDiv.append("<textarea id='getrecords'></textarea>");
											}
									});
								});

								mainDiv.append(typeDropDown);
							}
						});
					};

	var createDropDownList = function (classs, id, optionList, multiplee=false){
							var combo = jQuery("<select></select>").attr("id", id).attr("class", classs);
							if(multiplee)
							{
								combo.attr("multiple", "multiple");
							}

							combo.append("<option>Select</option>");

							jQuery.each(optionList, function (i, el) {

								if(typeof(el) == 'object')
									el = el.name;

								combo.append("<option value='" + i + "'>" + el + "</option>");
							});

							return combo;
						}
	
	var typeList	= new getTypeList(clientApp);
});
*/
