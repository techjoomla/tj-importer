// literal pattern

var importerUi = {

	hot : '',
	batchId : '',
	colName : '',
	colFields : '',
	trialCall : 0,
	trialCallLimit : 4,
	postItemSize : '',
	fetchItemSize : '',
	batchColumns : '',
	batchDetails : '',
	colProperties : '',
	checkTempItems :[],
	initialBtnEvent : '',
	textColorNew : false,
	batchTempRecords : [],
	batchClientRecords : [],
	batchTempInvalid : [],
	validateTempItems :[],
	processStartTime : '',
	primaryKey		: '',

	showModalFirst : function (thiss){

		if(thiss.id == 'load-batch')
		{
			jQuery('#load-batch-model').modal('show');
			importerUi.initialBtnEvent = thiss.id;
			importerUi.getBatchesList(document.getElementById('clientApp').value);
		}
		else
		{
			jQuery('#step-one-model #modal-title-1').text(thiss.getAttribute("modal-title-set"));
			jQuery('#step-one-model').modal('show');
			importerUi.initialBtnEvent = thiss.id;
			importerUi.step1();
		}
	},

	dismissModal : function (thiss){
		let eventForr	= '#' + thiss.getAttribute("for");
		let modalBody	= eventForr + " .modal-body";
		jQuery(modalBody).html("");
		jQuery("#fieldButton").remove();
		jQuery(eventForr).modal('hide');
	},

	getBatchesList : function(clientApp)
	{
		let promise = importerService.getBatchesList(clientApp);

		promise.fail(
			function() {
				// alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
				importerUi.showErrorBox(promise);
			}
		).done(
			function() {
				let batchesInfo	= jQuery.parseJSON(promise.responseText);
				importerUi.showBatchesList(batchesInfo);

				return;
			}
		);
	},

	showErrorBox : function (errMsg, functionName, param1 = 0, param2 = 0, param3 = 0, param4 = 0)
	{
		var displayErrorMsg = Joomla.JText._('COM_IMP_DEFAULT_ERROR_DESC');

		if (errMsg.status)
		{
			displayErrorMsg = errMsg.status + " " + errMsg.statusText;
		}

		if (errMsg.status == 502 && (importerUi.trialCall <= importerUi.trialCallLimit) && functionName != '')
		{
			console.log(importerUi.trialCall + "  fun name - " + functionName);
			importerUi.trialCall++;

			switch (functionName)
			{
				case "validateTempRecords" :
							importerUi.validateTempRecords(param1, param2, param3, param4);
						break;
				case "saveTempRecords" :
							importerUi.saveTempRecords(param1, param2, param3, param4);
						break;
				case "importTempRecords" :
							importerUi.importTempRecords(param1, param2, param3, param4);
						break;
				case "getRecordsTemp" :
							importerUi.getRecordsTemp(param1, param2, param3, param4);
						break;
				case "getRecordsClient" :
							importerUi.getRecordsClient(param1, param2, param3, param4);
						break;
				case "updateTempRecords" :
							importerUi.updateTempRecords(param1, param2, param3, param4);
						break;
				case "updateTempRecordsAfterImport" :
							importerUi.updateTempRecordsAfterImport(param1, param2, param3, param4);
						break;
			}

			return;
		}

		swal({
			  title: Joomla.JText._('COM_IMP_ERROR_MSG'),
			  text: displayErrorMsg,
			  type: "error",
			  showCancelButton: true,
			  confirmButtonColor: "#DD6B55",
			  cancelButtonText: Joomla.JText._('COM_IMP_TAKE_TO_STEP_ONE'),
			  confirmButtonText: Joomla.JText._('COM_IMP_RELOAD_PAGE'),
			  closeOnConfirm: false,
			  closeOnCancel: false
			},
			function(isConfirm){
			  if (isConfirm) {
				location.reload();
			  } else {
				importerUi.goFirst();
			  }
			});
	},

	showBatchesList : function (batchesInfo)
	{
		var batchesHtml = jQuery("<div></div>").attr("id", "batches-div");

		if(batchesInfo.totalBatches)
		{
			var dynamicTr	= '<tr>';
				dynamicTr +=	'<th>' + Joomla.JText._('COM_IMP_BATCHES_TH_NAME') + '</th>';
				dynamicTr +=	'<th>' + Joomla.JText._('COM_IMP_BATCHES_TH_CRE_DATE') + '</th>';
				dynamicTr +=	'<th>' + Joomla.JText._('COM_IMP_BATCHES_TH_MOD_DATE') + '</th>';
				dynamicTr +=	'<th>' + Joomla.JText._('COM_IMP_BATCHES_TH_CRE_USER') + '</th>';
				dynamicTr += '</tr>';

			for(i=0; i < batchesInfo.batches.length; i++)
			{
				dynamicTr += '<tr>';
				let batchLink = 'index.php?option=com_importer&view=importer&layout=handson&batch_id=' + batchesInfo.batches[i].id;
				let	batchName = (batchesInfo.batches[i].batch_name) ? batchesInfo.batches[i].batch_name : 'Unnamed';
				dynamicTr += '<td><a href=' + batchLink + ' title="Click to edit the records in this batch">' + batchName + '</a></td>';
				dynamicTr += '<td>' + batchesInfo.batches[i].created_date + '</td>';
				dynamicTr += '<td>' + batchesInfo.batches[i].updated_date + '</td>';
				dynamicTr += '<td>' + batchesInfo.batches[i].created_user + '</td>';
				dynamicTr += '</tr>';
			}

			var batchesTable 	= "<table class='batches-table'>" + dynamicTr + "</table>";
			var infoDiv			= "<p>"  + Joomla.JText._('COM_IMP_TOTAL_BATCHES_FOR') + "<b>" + document.getElementById('clientApp').value + "</b> are <b>" + batchesInfo.totalBatches + "</b></p>";

			jQuery('#load-batch-content').append(infoDiv);
			jQuery('#load-batch-content').append(batchesTable);
		}
		else
		{
			var infoDiv			= "<h3>" + Joomla.JText._('COM_IMP_NO_BATCHES_FOR') + document.getElementById('clientApp').value + "</h3>";

			jQuery('#load-batch-content').append(infoDiv);
		}

	},

	step1 : function(){
		importerService.clientApp = jQuery("#clientApp").val();

		let batchNameBox = importerUi.createTextbox(Joomla.JText._('COM_IMP_BATCH_NAME_LABEL'), 'JForm[batchName]', '', 'batchName', '')
		jQuery("#step1").append(batchNameBox);

		let promise = importerService.getTypeList();

		promise.fail(
			function() {
				//alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
				importerUi.showErrorBox(promise);
			}
		).done(
			function() {
				let clientTypesObj	= jQuery.parseJSON(promise.responseText);

				if(Object.keys(clientTypesObj).length > 1){
					let typeDropDown	= importerUi.createDropDownList(Joomla.JText._('COM_IMP_BATCH_TYPES_LABEL'), 'JForm["typeList"]', '', 'typeList', clientTypesObj, false, true);

					typeDropDown.on('change', importerUi.appendFieldList);
					jQuery("#step1").append(typeDropDown);
				}else{
					importerUi.appendFieldList('', Object.keys(clientTypesObj)[0]);
				}
			}
		);
	},

	appendFieldList : function (event, singleType=''){
			
			let typeSelected	= (event) ? jQuery("option:selected", this).val() : singleType;

			if (typeSelected == '')
			{
				jQuery("#fieldListDiv").remove();
				jQuery(".fieldButton").remove();
				jQuery("#idTextAreaDiv").remove();
				return;
			}

			importerUi.showProgress("Please wait..", 50);
			let promise			= importerService.getFieldList(typeSelected);

			promise.fail(
				function() {
					//~ alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
					importerUi.showErrorBox(promise);
				}
			).done(
				function() {
					let clientFieldsObj	= jQuery.parseJSON(promise.responseText);

					var clientFieldsObject = {};

					for(i=0 ; i < clientFieldsObj.length; i++ )
					{
						clientFieldsObject[clientFieldsObj[i].id] = clientFieldsObj[i].name;
					}

					console.log(clientFieldsObject);

					jQuery("#fieldListDiv").remove();
					jQuery(".fieldButton").remove();
					jQuery("#idTextAreaDiv").remove();

					let submitButton = importerUi.createButton('JForm["fieldButton"]', 'fieldButton btn .btn-success', 'fieldButton', 'Submit');
					submitButton.on('click', importerUi.submitBatch);

					if(importerUi.initialBtnEvent == 'edit-data'){
						let fieldDropDown	= importerUi.createDropDownList(Joomla.JText._('COM_IMP_BATCH_FIELDS_LABEL'), 'JForm["fieldList"]', '', 'fieldList', clientFieldsObject, true);
						jQuery("#step1").append(fieldDropDown);

						let idTextArea = importerUi.createTextArea(Joomla.JText._('COM_IMP_BATCH_RECORD_SELECTOR_LABEL'), 'JForm["idTextArea"]', '', 'idTextArea', 'Submit');
						jQuery("#step1").append(idTextArea);
					}

					jQuery("#modal-footer-1").append(submitButton);
					importerUi.doneProgress();
				}
			);
		},

	submitBatch : function(){
			let batchName		= jQuery("#batchName").val();
			let typeSelected	= jQuery("select#typeList").val();
			let fieldsSelected	= jQuery("select#fieldList").val();
			let recordsSelected = jQuery("#idTextArea").val();

			if(importerUi.initialBtnEvent == 'add-data' && batchName.trim() == '')
			{
				swal("Error!", "Please provide batch name", "error");
				return;
			}
			else if (importerUi.initialBtnEvent == 'edit-data')
			{
				var errorString = '';
				
				if (batchName.trim() == '')
				{
					errorString += "<br/>Please provide batch name";
				}
				
				if (recordsSelected.trim() == '')
				{
					errorString += "<br/>Please provide record identifiers";
				}

				if (errorString.trim())
				{
					swal({
						  title: "<h3>Error!</h3>",
						  text: errorString,
						  html: true
						});
					return;
				}
			}

			let batchParams	= {
					type:typeSelected,
					batchAction : importerUi.initialBtnEvent,
					columns:jQuery.extend({}, fieldsSelected)
				};

			let promise = importerService.saveBatch(batchParams, recordsSelected, batchName);

			promise.fail(
				function() {
					//~ alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
					importerUi.showErrorBox(promise);
				}
			).done(
				function() {
					let clientFieldsObj	= jQuery.parseJSON(promise.responseText);
					window.location = "index.php?option=com_importer&view=importer&layout=handson&batch_id=" + clientFieldsObj;
				}
			);
		},

	getBatch : function (){
			let batchDetailsPromise = importerService.getBatch(this.batchId);
			batchDetailsPromise.fail(
				function() {
					//~ alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
					importerUi.showErrorBox(promise);
				}
			).done(
				function() {
					let batchDetailsObj	= jQuery.parseJSON(batchDetailsPromise.responseText);
					let getColumns		= importerUi.getColumns(batchDetailsObj);
					importerUi.batchDetails = batchDetailsObj;
				}
			);
		},

	getColumns : function(batchDetailsObj){
			importerService.clientApp = batchDetailsObj.client;
			importerService.typeSelected = batchDetailsObj.params.type;
			let promise = importerService.getFieldList(batchDetailsObj.params.type, batchDetailsObj.params.columns);
			promise.fail(
				function() {
					//~ alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
					importerUi.showErrorBox(promise);
				}
			).done(
				function() {
					let batchColumnsObj	= jQuery.parseJSON(promise.responseText);
					this.batchColumns	= batchColumnsObj;

					// For v1.1 start
					for(i=0 ; i < batchColumnsObj.length; i++ )
					{
						if (batchColumnsObj[i].primary)
						{
							importerUi.primaryKey = batchColumnsObj[i].id;
							break;
						}
					}
					// For v1.1 end

					if(batchDetailsObj.start_id){
						importerUi.getRecordsClient(batchDetailsObj, batchColumnsObj);
					}else{
						importerUi.getRecordsTemp(batchDetailsObj, batchColumnsObj);
					}
				}
			);
		},

	getRecordsClient : function(batchDetailsObj, batchColumnsObj, startPoint = 0, dumbParam1 = 0){

			// For v1.1 start
			var clientFieldsObject = [];
			for(i=0 ; i < batchColumnsObj.length; i++ )
			{
				clientFieldsObject.push(batchColumnsObj[i].id);
			}
			// For v1.1 end

			let startIdStr = batchDetailsObj.start_id;
			let startIdArr = startIdStr.split(',');
			let startIdCnt = startIdArr.length;

			let endPoint	= importerUi.fetchItemSize + startPoint;

			let processStartTime = new Date().getTime();
			importerUi.showEstimatedRemainingTime(startPoint, startIdCnt, processStartTime);

			let slicedStartIdArr = startIdArr.slice(startPoint, endPoint);
			let slicedStartIdStr = slicedStartIdArr.join();

			let promise = importerService.getRecordsList(batchDetailsObj.params.type, batchColumnsObj, slicedStartIdStr);

			promise.fail(
				function() {
					importerUi.showErrorBox(promise, "getRecordsClient", batchDetailsObj, batchColumnsObj, startPoint);
				}
			).done(
				function() {
					importerUi.trialCall = 0;
					if(startPoint < startIdCnt)
					{
						let batchRecordsObj	= jQuery.parseJSON(promise.responseText);

						if (batchRecordsObj != null)
						{
							importerUi.batchClientRecords = importerUi.batchClientRecords.concat(batchRecordsObj);
						}

						importerUi.showProgress(Joomla.JText._('COM_IMP_FETCHING_CLT_RECORDS'), ((startPoint/startIdCnt) * 100));

						importerUi.getRecordsClient(batchDetailsObj, batchColumnsObj, startPoint + importerUi.fetchItemSize);
					}
					else
					{
						importerUi.loadHandsonView(batchColumnsObj, importerUi.batchClientRecords);
					}
				}
			);
		},

	getRecordsTemp : function(batchDetailsObj, batchColumnsObj, tempOffset = 0, dumbParam1 = 0){

			let promise = importerService.getRecordsTemp(batchDetailsObj.id, tempOffset);
			promise.fail(
				function() {
					//~ alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
					importerUi.showErrorBox(promise, "getRecordsTemp", batchDetailsObj, batchColumnsObj, tempOffset);
				}
			).done(
				function() {
					importerUi.trialCall = 0;
					let batchRecordsObj	= jQuery.parseJSON(promise.responseText);

					importerUi.batchTempRecords = importerUi.batchTempRecords.concat(batchRecordsObj.items);
					importerUi.batchTempInvalid = importerUi.batchTempInvalid.concat(batchRecordsObj.invalid);
					importerUi.validateTempItems = importerUi.validateTempItems.concat(batchRecordsObj.validated);
					importerUi.checkTempItems = importerUi.checkTempItems.concat(batchRecordsObj.validated);

					let processStartTime = new Date().getTime();
					importerUi.showEstimatedRemainingTime(tempOffset, batchRecordsObj.count, processStartTime);

					if(importerUi.batchTempRecords.length <  batchRecordsObj.count)
					{
						importerUi.showProgress(Joomla.JText._('COM_IMP_FETCHING_TEMP_RECORDS'), ((importerUi.batchTempRecords.length/batchRecordsObj.count)*100));
						importerUi.getRecordsTemp(batchDetailsObj, batchColumnsObj, importerUi.batchTempRecords.length);
					}
					else
					{
						importerUi.showProgress(Joomla.JText._('COM_IMP_FETCHING_TEMP_RECORDS'), 100);
						importerUi.loadHandsonView(batchColumnsObj, importerUi.batchTempRecords);
					}

					return;
				}
			);
		},

	invalidRowRenderer : function (instance, td, row, col, prop, value, cellProperties){
		    Handsontable.renderers.TextRenderer.apply(this, arguments);

			if(importerUi.batchTempInvalid[row])
			{
				let invaFields		= JSON.parse(importerUi.batchTempInvalid[row]);
				var invalidArray	=  Object.keys(invaFields).map(function(k) { return invaFields[k] });

				td.style.background = '#CEC';

				if(invalidArray.includes(prop))
				{	
					td.style.fontWeight = 'bold';
					td.style.color = 'red';
					td.style.background = '#faa1a1';
				}
			}
		},

	ajaxCallFunction : function(query, process){

			var query = query;
			var queryVal	= query.split("|");
			var queryValue	= queryVal[queryVal.length - 1];

			var displayValue = [queryValue];

			console.log(displayValue);

			process(query);

			if (queryValue.trim() == '')
			{
				return;
			}

			let promise = importerService.getSuggestions(queryValue.trim());
			promise.fail(
				function() {
					importerUi.showErrorBox();
				}
			).done(
				function() {
					let suggestions	= jQuery.parseJSON(promise.responseText);
					process(suggestions);

					return;
				}
			);
		},

	loadHandsonView : function(batchColumnsObj, batchRecordsObj=''){

			// For v1.1 start
			var clientFieldsNames = [];
			var clientFieldsProps = [];

			for(i=0 ; i < batchColumnsObj.length; i++ )
			{
				clientFieldsNames.push(batchColumnsObj[i].name);

				var propObj = {'data' : batchColumnsObj[i].id, 'readOnly' : batchColumnsObj[i].readOnly};

				if (batchColumnsObj[i].type != "text")
				{
					propObj.type = batchColumnsObj[i].type;
					//~ propObj.source	= batchColumnsObj[i].option != null ? batchColumnsObj[i].option : ajaxCallFunction;
					propObj.source	= batchColumnsObj[i].option != null ? batchColumnsObj[i].option : importerUi.ajaxCallFunction;
					propObj.strict	= false;
				}

				clientFieldsProps.push(propObj);
			}
			// For v1.1 end

console.log(clientFieldsProps);

			var importBtnName = Joomla.JText._('COM_IMP_IMPORT_BTN_NAME_UPDATE');
			let handontableParams = {};
			handontableParams.rowHeaders	= true;
			//handontableParams.colHeaders	= batchColumnsObj.colName;
			handontableParams.colHeaders	= clientFieldsNames;

			//handontableParams.columns		= batchColumnsObj.colProperties;
			handontableParams.columns		= clientFieldsProps;
			handontableParams.contextMenu	= true;
			handontableParams.stretchH		= 'none';

			if (importerUi.batchDetails.params.batchAction == 'add-data')
			{
				handontableParams.minSpareRows	= 1;
				importBtnName = Joomla.JText._('COM_IMP_IMPORT_BTN_NAME_IMPORT');
			}

			/*
			 * This loop is to remove 'backslash' from string value
			*/
			for(i = 0; i < batchRecordsObj.length; i++ )
			{
				let thisBatchRecObj = batchRecordsObj[i];
				batchRecordsObj[i] = jQuery.parseJSON(JSON.stringify(batchRecordsObj[i]));
			}

			if(batchRecordsObj)
			{
				handontableParams.data	= batchRecordsObj;
			}

			handontableParams.cells = function (row, col, prop){
				var cellProperties = {};

				if (importerUi.batchTempInvalid.length && importerUi.batchTempInvalid[row]){
					cellProperties.renderer = importerUi.invalidRowRenderer; // uses function directly
				}

				return cellProperties;
			};

			importerUi.doneProgress("");

			// Below two lines to load handsontable
			let container	= document.getElementById('example');
			importerUi.hot	= new Handsontable(container, handontableParams);

			importerUi.hot.updateSettings({
					afterChange: function(changes, source) {
							//console.log(source);
							importerUi.checkTempItems = [];
							for(i = 0; i < changes.length; i++){
								if(changes[i][2] != changes[i][3]){
									document.getElementById("import-btn").disabled = true;
								}
							}
						},
					modifyData : function(row, column, valueHolder,ioMode) {
							//console.log(row + "--- " + column + "--- " + valueHolder + "--- " + ioMode);
						}
				});

			let validateButton = importerUi.createButton('JForm["validate"]', 'validate', 'validate', 'Validate');
			validateButton.on('click', importerUi.saveTempRecords);

			let saveTempButton = importerUi.createButton('JForm["saveTemp"]', 'saveTemp', 'saveTemp', 'Save Progress');
			saveTempButton.on('click', importerUi.saveTempRecords);

			let backButton = importerUi.createButton('JForm["back"]', 'back pull-right', 'back', 'Cancel');
			backButton.on('click', importerUi.goFirst);

			let importButton = importerUi.createButton('JForm["import"]', 'import', 'import-btn', importBtnName);
			importButton.on('click', importerUi.importTempRecords);

			let batchDetViewBtn = importerUi.createButton('JForm["batchView"]', 'batchView', 'batchView', 'View Batch Details');
			batchDetViewBtn.on('click', importerUi.viewBaatchStatus);

			jQuery("#importer-buttons-container").append(batchDetViewBtn);
			jQuery("#importer-buttons-container").append(saveTempButton);
			jQuery("#importer-buttons-container").append(validateButton);
			jQuery("#importer-buttons-container").append(importButton);
			jQuery("#importer-buttons-container").append(backButton);

			let filteredInvalid		= importerUi.batchTempInvalid.filter(importerUi.checkEmptyArray);
			let filteredValidated	= importerUi.validateTempItems.filter(importerUi.checkEmptyArray);

			if(importerUi.batchTempInvalid.length && (filteredInvalid.length == 0) && (filteredValidated.length == 0)){
				document.getElementById("import-btn").disabled = false;
			}else{
				document.getElementById("import-btn").disabled = true;
			}

			delete importerUi.batchTempRecords;
			delete importerUi.batchClientRecords;
		},

	viewBaatchStatus : function ()
	{
		let tableRecordsCount	= importerUi.hot.countRows() - importerUi.hot.countEmptyRows() ;

		let promise = importerService.getTempStatus(importerUi.batchDetails.id);
			promise.fail(
				function() {
					//~ alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
					importerUi.showErrorBox(promise);
				}
			).done(
				function() {
					let temDetails	= jQuery.parseJSON(promise.responseText);
					jQuery('#batchStatus').modal('show');
					jQuery('#batchStatusTitle').text(importerUi.batchDetails.batch_name);
					jQuery('#batchStatusBody').append("<ul class='list-group'></ul>");
					jQuery('#batchStatusBody ul').append("<li class='list-group-item'>" + Joomla.JText._('COM_IMP_TOT_CSV_REC') + "<b>" + tableRecordsCount + "</b></li>");
					jQuery('#batchStatusBody ul').append("<li class='list-group-item'>" + Joomla.JText._('COM_IMP_TOT_TMP_REC') + "<b>" + temDetails.itemsTotal + "</b></li>");
					jQuery('#batchStatusBody ul').append("<li class='list-group-item'>" + Joomla.JText._('COM_IMP_TOT_VLD_REC') + "<b>" + temDetails.validatedTotal + "</b></li>");
					jQuery('#batchStatusBody ul').append("<li class='list-group-item'>" + Joomla.JText._('COM_IMP_TOT_INVLD_REC') + "<b>" + temDetails.invalidTotal + "</b></li>");
					jQuery('#batchStatusBody ul').append("<li class='list-group-item'>" + Joomla.JText._('COM_IMP_TOT_IMP_REC') + "<b>" + temDetails.importedTotal + "</b></li>");
				}
			);
	},

	checkEmptyArray : function (value){
			if(parseInt(value) == value){
				return value == 0;
			}else{
				return value != '';
			}
		},

	goFirst : function(){
			window.location = "index.php?option=com_importer&view=importer&clientapp=" + importerUi.batchDetails.client;
		},

	showEstimatedRemainingTime : function(startPoint, totalRecords, processStartTime)
	{
		let remainingRecords = totalRecords - startPoint;

		let remainingRecordsLot = remainingRecords / importerUi.fetchItemSize ;

		if (importerUi.processStartTime)
		{
			let currTime				= new Date().getTime();
			let timeConsumed			= currTime - importerUi.processStartTime;
			let estimatedTimeRemaining	= remainingRecordsLot * timeConsumed;

			let eTimeSec = (estimatedTimeRemaining > 1000 ? Math.round(estimatedTimeRemaining/1000) : Math.round((estimatedTimeRemaining/1000) * 100) / 100);
			var displayTime = eTimeSec + " sec";

			if (eTimeSec > 60)
			{
				let eTimeMin = Math.round((eTimeSec / 60) * 100) / 100;
				
				let min = eTimeMin.toString().split(".")[0];
				let sec = eTimeMin.toString().split(".")[1] ? eTimeMin.toString().split(".")[1] : "00" ;
				displayTime = ((parseInt(sec) > 60) ? ((parseInt(min) + 1) + " min " +  (parseInt(sec) - 60) + " sec" ) : ( min + " mins " + sec + " sec" ));
			}

			jQuery("#progress-text #progress-time-span").text("Estimated time remaining - " + displayTime);
		}
		else
		{
			jQuery("#progress-text #progress-time-span").text("Calculating estimated time");
		}

		importerUi.processStartTime = processStartTime;
		return;
	},

	showProgress : function(text, percentage){
			let showPercent = (percentage > 100) ? 100 : Math.round((percentage * 100) / 100);

			jQuery("#progress-text #progress-text-span").text(text + " " + showPercent + "% done");
			jQuery("#pg-bar").css('width', showPercent + "%");

			if(!(jQuery(".fade-div").hasClass("fadded")))
			{
				jQuery(".fade-div").addClass("fadded");
			}

			if(jQuery("#progress-text").hasClass("text-hide"))
			{
				jQuery("#progress-text").toggleClass("text-hide text-show");
			}
		},

	doneProgress : function(text)
		{
			importerUi.processStartTime = '';
			jQuery("#pg-bar").css('width', "0");
			jQuery(".fade-div").removeClass("fadded");
			jQuery("#progress-text").toggleClass("text-hide text-show");
		},

	importTempRecords : function (event, itemStart=0, dumbParam1 = 0, dumbParam2 = 0){
			var processingStatusText	= Joomla.JText._('COM_IMP_REC_UPDATING');
			var successStatusText		= Joomla.JText._('COM_IMP_REC_UPDATED');
			let allItems				= importerUi.hot.getSourceData();
			let recordsCount			= (importerUi.hot.getSourceData()).length;
			let itemsEnd				= importerUi.postItemSize + itemStart;
			let pgWidth					= ((itemStart) / recordsCount)*(100);

			if (importerUi.batchDetails.params.batchAction == 'add-data')
			{
				processingStatusText	= Joomla.JText._('COM_IMP_REC_IMPORTING');
				successStatusText		= Joomla.JText._('COM_IMP_REC_IMPORTED');
			}

			importerUi.showProgress(processingStatusText, pgWidth);

			let processStartTime = new Date().getTime();
			importerUi.showEstimatedRemainingTime(itemStart, recordsCount, processStartTime);

			if(itemStart >= recordsCount)
			{
				importerUi.hot.render();
				importerUi.doneProgress('');
				document.getElementById("import-btn").disabled = true;

				let filteredInvalid		= importerUi.batchTempInvalid.filter(importerUi.checkEmptyArray);

				if(filteredInvalid.length){
					swal("Attention!", "Few records had issue while saving. Please validate again and import the batch", "warning");
				}else{
					swal(successStatusText, '', "success");

					if(event.target.id === 'import-btn')
					{
						importerUi.batchDetails.import_status = 1;
						importerUi.updateBatch(event);
					}
				}

				return;
			}

			let checkItems	= allItems.slice(itemStart, itemsEnd);

			let promise = importerService.importTempRecords(checkItems, importerUi.batchDetails);
			promise.fail(
				function() {
					importerUi.showErrorBox(promise, "importTempRecords", event, itemStart);
				}
			).done(
				function() {
					importerUi.trialCall = 0;
					let importedDetails		= jQuery.parseJSON(promise.responseText);
					let importedRecDetails	= importedDetails.records;
					let importedInvalidDetails	= importedDetails.invalid;

					/* code to highlight invalid rows after import */
					let chekcingArray	= [];
					let arrayChk		= jQuery.map(importedInvalidDetails, function(value, index) {return [value];});

					for (i = 0; i < arrayChk.length; i++){
						if(arrayChk[i]){
							chekcingArray.push(JSON.stringify(jQuery.extend({}, arrayChk[i])));
						}else{
							chekcingArray.push("");
						}
					}

					if(itemStart == 0){
						importerUi.batchTempInvalid = [];
					}

					importerUi.batchTempInvalid = importerUi.batchTempInvalid.concat(chekcingArray);

					// Assigning temp table id's to handsontable data
					for (i = 0; i < importedRecDetails.length; i++){
						if (importedRecDetails[i][importerUi.primaryKey] !== null){
							importerUi.hot.getSourceData()[itemStart + i][importerUi.primaryKey] = importedRecDetails[i][importerUi.primaryKey];
						}
					}

					importerUi.updateTempRecordsAfterImport(event, itemStart, importedRecDetails, importedInvalidDetails);
				}
			);
		},

	updateTempRecordsAfterImport : function(event, itemStart, importedRecDetails, importedInvalidDetails){
			var processingStatusText	= Joomla.JText._('COM_IMP_REC_UPDATING');
			let recordsCount			= (importerUi.hot.getSourceData()).length;
			let completedItem			= importerUi.postItemSize / 2;
			let pgWidth					= ((itemStart + completedItem) / recordsCount)*(100);

			if (importerUi.batchDetails.params.batchAction == 'add-data')
			{
				processingStatusText	= Joomla.JText._('COM_IMP_REC_IMPORTING');
			}

			importerUi.showProgress(processingStatusText, pgWidth);

			let promise		= importerService.saveTempRecords(importedRecDetails, importerUi.batchDetails, importedInvalidDetails, true);

			promise.fail(
				function() {
					importerUi.showErrorBox(promise, "updateTempRecordsAfterImport", event, itemStart, importedRecDetails, importedInvalidDetails);
				}
			).done(
				function() {
					let updatedStatus	= jQuery.parseJSON(promise.responseText);

					if(updatedStatus){
						importerUi.importTempRecords(event, (itemStart + importerUi.postItemSize));
					}
				}
			);
		},

	saveTempRecords : function(event, itemStart=0, dumbParam1 = 0, dumbParam2 = 0){
			let allItems		= importerUi.hot.getSourceData();
			let recordsCount	= (importerUi.hot.getSourceData()).length;
			let itemsEnd		= importerUi.postItemSize + itemStart;
			let pgWidth			= ((itemStart) / recordsCount)*(100);
			var pgText			= Joomla.JText._('COM_IMP_REC_SAVING_TEMP');

			//jQuery("#pg-bar").css('width', pgWidth + "%");
			if(event.target.id === 'validate')
			{
				pgText			= Joomla.JText._('COM_IMP_REC_VALIDATING');
			}

			importerUi.showProgress(pgText, pgWidth);

			let processStartTime = new Date().getTime();
			importerUi.showEstimatedRemainingTime(itemStart, recordsCount, processStartTime);

			let checkItems	= allItems.slice(itemStart, itemsEnd);
			let promise		= importerService.saveTempRecords(checkItems, importerUi.batchDetails);

			promise.fail(
				function() {
					//alert(Joomla.JText._('COM_IMP_ERROR_MSG'))
					//~ swal(Joomla.JText._('COM_IMP_ERROR_MSG'));
					importerUi.showErrorBox(promise, "saveTempRecords", event, itemStart);
				}
			).done(
				function() {
					importerUi.trialCall = 0;
					let tempIds	= jQuery.parseJSON(promise.responseText);

					// Assigning temp table id's to handsontable data
					for (i = 0; i < tempIds.length; i++){
						if(tempIds[i] !== null){
							importerUi.hot.getSourceData()[itemStart + i].tempId = tempIds[i];
						}
					}

					if (itemStart >= recordsCount){
						if(event.target.id === 'validate'){
							let filteredInvalid		= importerUi.batchTempInvalid.filter(importerUi.checkEmptyArray);

							if(filteredInvalid.length){
								swal("Attention!", "There are few invalid records. Please correct it and then validate again", "warning");
							}else{
								swal(Joomla.JText._('COM_IMP_REC_VALIDATED'), '', "success");
							}
						}else{
							// alert(Joomla.JText._('COM_IMP_REC_SAVED_TEMP'));
							swal(Joomla.JText._('COM_IMP_REC_SAVED_TEMP'), '', "success");
						}
						importerUi.doneProgress('');
						importerUi.batchDetails.import_status = '';
						importerUi.updateBatch(event.target.id); 
					}else{
						if(event.target.id === 'validate'){
							importerUi.validateTempRecords(event, itemStart);
						}else{
							importerUi.saveTempRecords(event, (itemStart + importerUi.postItemSize));
						}
					}
				}
			);
		},

	validateTempRecords : function(event, itemStart, dumbParam1 = 0, dumbParam2 = 0){
			let allItems		= importerUi.hot.getSourceData();
			let recordsCount	= (importerUi.hot.getSourceData()).length;
			let itemsEnd		= importerUi.postItemSize + itemStart;
			let checkItems		= allItems.slice(itemStart, itemsEnd);
			let completedItem	= importerUi.postItemSize / 3;
			let pgWidth			= ((itemStart + completedItem) / recordsCount)*(100);

			importerUi.showProgress(Joomla.JText._('COM_IMP_REC_VALIDATING'), pgWidth);

			let promise = importerService.validateRecords(checkItems, importerUi.batchDetails);

			promise.fail(
				function() {
					//~ alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
					importerUi.showErrorBox(promise, "validateTempRecords", event, itemStart);
				}
			).done(
				function() {
					importerUi.trialCall = 0;
					let invalidRecObj	= jQuery.parseJSON(promise.responseText);
					let chekcingArray	= [];
					let arrayChk		= jQuery.map(invalidRecObj, function(value, index) {return [value];});

					for (i = 0; i < arrayChk.length; i++){
						if(arrayChk[i]){
							chekcingArray.push(JSON.stringify(jQuery.extend({}, arrayChk[i])));
						}else{
							chekcingArray.push("");
						}
					}

					if(itemStart == 0){
						importerUi.batchTempInvalid = [];
					}

					importerUi.batchTempInvalid = importerUi.batchTempInvalid.concat(chekcingArray);
					importerUi.updateTempRecords(event, itemStart, invalidRecObj);
				}
			);
		},

	updateTempRecords : function(event, itemStart, invalidData, dumbParam = 0){
			let recordsCount	= (importerUi.hot.getSourceData()).length;
			let completedItem	= importerUi.postItemSize * (2/3);
			let pgWidth			= ((itemStart + completedItem) / recordsCount)*(100);

			var pgText			= Joomla.JText._('COM_IMP_REC_SAVING_TEMP');

			//jQuery("#pg-bar").css('width', pgWidth + "%");
			if(event.target.id === 'validate')
			{
				pgText			= Joomla.JText._('COM_IMP_REC_VALIDATING');
			}
			
			importerUi.showProgress(pgText, pgWidth);

			let promise		= importerService.saveTempRecords('', '', invalidData);

			promise.fail(
				function() {
					//~ alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
					importerUi.showErrorBox(promise, "updateTempRecords", event, itemStart, invalidData);
				}
			).done(
				function() {
					let updatedStatus	= jQuery.parseJSON(promise.responseText);

					if(updatedStatus){
						importerUi.saveTempRecords(event, (itemStart + importerUi.postItemSize));
					}
				}
			);
		},

	updateBatch : function(event, importStatusUpdate = 0){
			let promise = importerService.updateBatch(importerUi.batchDetails);
			promise.fail(
				function() {
					//~ alert(Joomla.JText._('COM_IMP_ERROR_MSG'))
					importerUi.showErrorBox(promise);
				}
			).done(
				function() {
					importerUi.hot.render();

						//location.reload();
						let filteredInvalid = importerUi.batchTempInvalid.filter(importerUi.checkEmptyArray);

						if(importerUi.batchTempInvalid.length && filteredInvalid.length == 0 && event == 'validate'){
							document.getElementById("import-btn").disabled = false;
						}else{
							document.getElementById("import-btn").disabled = true;
						}
				}
			);
		},

	createTextbox : function (label='', name, classs, id, placeholder=''){
			let $comboLabel = '';
			let $comboEle = '';
			let $combo = '';

			$combo = jQuery("<div></div>").attr("class", classs).attr("id", id+"Div");

			if(label){
				$comboLabel = jQuery("<label></label>").attr("for", name).attr("class", "span2").text(label);
			}

			$comboEle = jQuery("<input></input>").attr("type", "text").attr("id", id).attr('name', name).attr("class", "span5 required").attr('placeholder', placeholder).attr('required', true);
			$comboLabel.appendTo($combo);
			$comboEle.appendTo($combo);

			return $combo;
		},

	createDropDownList : function (label='', name, classs, id, optionList, multiplee=false, defaultOption=false){
			let $comboLabel = '';
			let $comboEle = '';
			let $combo = '';

			$combo = jQuery("<div></div>").attr("class", classs).attr("id", id+"Div");

			if(label){
				$comboLabel = jQuery("<label></label>").attr("for", name).attr("class", "span2").text(label);
			}

			$comboEle = jQuery("<select></select>").attr("id", id).attr('name', name).attr("class", "span5");
			if(multiplee){
				$comboEle.attr("multiple", "multiple");
			}
			
			if(defaultOption)
			{
				$comboEle.append("<option value=''>Select</option>");
			}

			jQuery.each(optionList, function (i, el) {
				if(typeof(el) == 'object'){
					el = el.name;
				}
				$comboEle.append("<option value='" + i + "'>" + el + "</option>");
			});

			$comboLabel.appendTo($combo);
			$comboEle.appendTo($combo);

			return $combo;
		},

	createButton : function (name, classs, id, textDisplay){
			let combo = jQuery("<button></button>").attr("id", id).attr("class", classs).attr('name', name).text(textDisplay);

			return combo;
		},

	createTextArea : function (label, name, classs, id, placeholder){
			let $comboLabel = '';
			let $comboEle = '';
			let $combo = '';

			$combo = jQuery("<div></div>").attr("class", classs).attr("id", id+"Div");

			if(label){
				$comboLabel = jQuery("<label></label>").attr("for", name).attr("class", "span2").text(label);
			}

			$comboEle = jQuery("<textarea></textarea>").attr("id", id).attr('name', name).attr("class", "span3").attr('placeholder', placeholder);

			$comboLabel.appendTo($combo);
			$comboEle.appendTo($combo);

			return $combo;
		}
	}

jQuery(document).ready(function(){
		jQuery("#wrap").css('min-height', 0);
		jQuery("#container").removeClass('span12').addClass('span6');

		let batchId = jQuery("#batchId").val();
		if(batchId){
			importerUi.showProgress(Joomla.JText._('COM_IMP_FETCHING_CLT_RECORDS'), 1);
			
			importerUi.postItemSize = importerUi.fetchItemSize = parseInt(jQuery("#pfSize").val());
			
			importerUi.batchId = batchId;
			importerUi.getBatch();
		}

	});
