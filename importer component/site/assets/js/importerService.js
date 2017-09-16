
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
						url: "index.php?option=com_api&app=importer_" + this.clientApp + "&resource=clienttypes&format=raw",
						headers: {'x-auth':'session'}
					});
			return clientTypes;
		},

	getBatchesList : function(clientApp){
			var batchesList	= jQuery.ajax({
						type: "GET",
						url: "index.php?option=com_api&app=importer&resource=batches&format=raw&clientapp=" + clientApp,
						headers: {'x-auth':'session'}
					});
			return batchesList;
		},

	getFieldList : function (typeSelected, fieldSelected, defaultValFields){
			var	clientApp	=  this.clientApp ;

			var clientColumns = jQuery.ajax({
					type: "GET",
					url: "index.php?option=com_api&app=importer_" + this.clientApp + "&resource=clientcolumns&format=raw&type=" + typeSelected,
					data : {fields : fieldSelected, defValFields : defaultValFields},
					headers: {'x-auth':'session'}
				});

			return clientColumns;
		},

	getFetchOptions : function()
		{
			var	clientApp	=  this.clientApp ;

			var clientOptions = jQuery.ajax({
					type: "GET",
					url: "index.php?option=com_api&app=importer_" + clientApp + "&resource=clientoptions&format=raw",
					data : {type : jQuery("select#typeList").val()},
					headers: {'x-auth':'session'}
				});

			return clientOptions;
		},

	getRecordsList : function(batchparams, columns, ids, startPoint, countall)
		{
			var	clientApp	=  this.clientApp ;

			var clientRecords = jQuery.ajax({
					type: "POST",
					url: "index.php?option=com_api&app=importer_" + clientApp + "&resource=clientrecords&format=raw",
					data : {'batchparams': batchparams, 'fields' : columns, 'ids' : ids, 'startPoint':startPoint, 'countall' : importerUi.countall, 'limit' : importerUi.fetchItemSize},
					headers: {'x-auth':'session'}
				});

			return clientRecords;
		},

	getRecordsTemp : function(batchId, itemOffset)
		{
			let tempRecords = jQuery.ajax({
					type: "GET",
					url: "index.php?option=com_api&app=importer&resource=item&format=raw",
					data : {batch_id: batchId, offset: itemOffset, limit : importerUi.fetchItemSize},
					headers: {'x-auth':'session'}
				});

			return tempRecords;
		},

	getTempStatus : function(batchId)
		{
			let tempRecords = jQuery.ajax({
					type: "GET",
					url: "index.php?option=com_api&app=importer&resource=item&format=raw",
					data : {batch_id: batchId, getStatus : 1},
					headers: {'x-auth':'session'}
				});

			return tempRecords;
		},

	getSuggestions : function(queryValue, queryProp)
		{
			let suggestions = jQuery.ajax({
						type: "GET",
						url: "index.php?option=com_api&app=importer_" + this.clientApp + "&resource=clientsuggestions&format=raw",
						data : {query : queryValue, batchType : importerUi.batchDetails.params.type, fieldId : queryProp},
						headers: {'x-auth':'session'}
					});

				return suggestions;
		},

	getDefaultValFields : function(typeSelected)
		{
			let defVals = jQuery.ajax({
						type: "GET",
						url: "index.php?option=com_api&app=importer_" + this.clientApp + "&resource=clientdefaultval&format=raw",
						data : {type : typeSelected},
						headers: {'x-auth':'session'}
					});

				return defVals;
		},

	saveBatch : function(batchParams, recordsSelected, batchName = '')
		{
			var	clientApp		= this.clientApp;
			var batch_name		= batchName;
			var client			= clientApp;
			var import_status	= 0;
			var created_date	= '';
			var updated_date	= '';
			var created_user		= document.getElementById("userId").value;	
			var params			= batchParams;
			var start_id		= '';

			var saveResult = jQuery.ajax({
								type : "POST",
								url : "index.php?option=com_api&app=importer&resource=batch&format=raw", 
								data: { 'JForm': {
											batch_name : batch_name, 
											client : client, 
											params : JSON.stringify(params),
											start_id : recordsSelected,
											created_user : created_user
										}
									},
								headers: {'x-auth':'session'}
							});

			return saveResult;
		},

	updateBatch : function(batchDetails)
		{
			var batchUpdated = jQuery.ajax({
					type: "POST",
					url: "index.php?option=com_api&app=importer&resource=batch&format=raw",
					data: {JForm:batchDetails},
					headers: {'x-auth':'session'}
				});
			
			return batchUpdated;
		},

	getBatch : function(batchId)
		{
			var batchDetails = jQuery.ajax({
					type: "GET",
					url: "index.php?option=com_api&app=importer&resource=batch&format=raw",
					data: {id:batchId},
					headers: {'x-auth':'session'}
				});
			
			return batchDetails;
		},

	saveTempRecords : function(records, batchDetails, invalidData = '', imported = 0)
		{
			var savedTemp = jQuery.ajax({
					type	: "POST",
					url		: "index.php?option=com_api&app=importer&resource=item&format=raw",
					data	:{
								records : JSON.stringify(records),
								batchDetails : JSON.stringify(batchDetails),
								invalidData :  JSON.stringify(invalidData),
								imported	: imported,
								primaryKey	: importerUi.primaryKey
							},
					headers: {'x-auth':'session'}
				});

			return savedTemp;
		},

	validateRecords : function(checkItems, batchDetails)
		{
			var validating  = jQuery.ajax({
					type: "POST",
					url: "index.php?option=com_api&app=importer_" + this.clientApp + "&resource=clientvalidate&format=raw",
					data: {records : JSON.stringify(checkItems), batchDetails : JSON.stringify(batchDetails)},
					headers: {'x-auth':'session'}
				});

			return validating;
		},

	importTempRecords : function(checkItems, batchDetails)
		{
			var importStatus  = jQuery.ajax({
					type: "POST",
					url: "index.php?option=com_api&app=importer_" + this.clientApp + "&resource=clientimport&format=raw",
					data: {records : JSON.stringify(checkItems), batchDetails : JSON.stringify(batchDetails)},
					headers: {'x-auth':'session'}
				});

			return importStatus;
		}
};
