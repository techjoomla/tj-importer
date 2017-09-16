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
	batchImageFields : {},
	batchClientRecords : [],
	batchClientRecordsStripped : [],
	clientFieldNames : [],
	batchTempInvalid : [],
	batchTempInvalidFirst : [''],
	validateTempItems :[],
	defaultColumnList :[],
	processStartTime : '',
	shiftedRow : '',
	primaryKey		: '',
	fetchall		: '',
	countall		: 0,
	customRendererFields : [],
	copyHeader	: 0,
	repFieldDetails : [],
	activeRow : 0,

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
		//console.log(repHandsontable);
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
		if (!errMsg)
		{
			return;
		}

		var displayErrorMsg = Joomla.JText._('COM_IMP_DEFAULT_ERROR_DESC');

		if (errMsg && errMsg.status)
		{
			displayErrorMsg = errMsg.status + " " + errMsg.statusText;
		}

		if (errMsg && errMsg.status == 502 && (importerUi.trialCall <= importerUi.trialCallLimit) && functionName != '')
		{
			//console.log(importerUi.trialCall + "  fun name - " + functionName);
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
		jQuery(".loading-img-importer").show();
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
			jQuery(".loading-img-importer").hide();
		}
		else
		{
			var infoDiv			= "<h3>" + Joomla.JText._('COM_IMP_NO_BATCHES_FOR') + document.getElementById('clientApp').value + "</h3>";

			jQuery('#load-batch-content').append(infoDiv);
		}

	},

	registerChosen : function(selectObject, callback = ''){

		if(callback){
			jQuery(".select-box-for-chosen", selectObject).chosen({width: "250px", allow_single_deselect:true}).on('change', callback);
		}else{
			jQuery(".select-box-for-chosen", selectObject).chosen({width: "250px", allow_single_deselect:true});
		}

		jQuery('.select-box-for-chosen', selectObject).on('chosen:showing_dropdown', function(evt, params) {
			if(jQuery("#step1").height() < jQuery(".chosen-drop", selectObject).height())
			{
				jQuery("#step1").height(jQuery("#step1").height() + jQuery(".chosen-drop", selectObject).height());
			}
		  });
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
					importerUi.registerChosen(typeDropDown, importerUi.appendFieldList);
					//typeDropDown.on('change', importerUi.appendFieldList);

					jQuery("#step1").append(typeDropDown);
				}else{
					importerUi.appendFieldList('', Object.keys(clientTypesObj)[0]);
				}

			}
		);
	},

	appendFieldList : function (event, singleType){

			let typeSelected	= (event) ? jQuery("option:selected", this).val() : singleType;

			jQuery("#fieldListDiv").remove();
			jQuery(".fieldButton").remove();
			jQuery("#idTextAreaDiv").remove();
			jQuery(".radio-div").remove();
			jQuery(".fetchoptions").remove();
			jQuery(".class-def-values").remove();

			if (typeSelected == '' && event)
			{
				return;
			}

			importerUi.showProgress("Please wait..", 50);
			let promise			= importerService.getFieldList(typeSelected, '', '');

			promise.fail(
				function() {
					//~ alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
					importerUi.showErrorBox(promise);
				}
			).done(
				function() {
					let clientFieldsObj	= jQuery.parseJSON(promise.responseText);

					var clientFieldsObject = {};

					var defFieldsArr = [];

					for(i=0 ; i < clientFieldsObj.length; i++ )
					{
						clientFieldsObject[clientFieldsObj[i].id] = clientFieldsObj[i].name;

						if (clientFieldsObj[i].defaultCol)
						{
							importerUi.defaultColumnList.push(clientFieldsObj[i].id);
						}

						if(clientFieldsObj[i].hasOwnProperty('defaultVal') && importerUi.initialBtnEvent == 'add-data')
						{
							let batchNameBox = importerUi.createTextbox(clientFieldsObj[i].name, clientFieldsObj[i].id , 'defaultValFields', '', '')
							
							if(clientFieldsObj[i].defaultVal)
							{
								jQuery("input[type=text]", batchNameBox).val(clientFieldsObj[i].defaultVal);
							}
							
							defFieldsArr.push(batchNameBox);
						}

					}

					if(importerUi.initialBtnEvent == 'add-data')
					{
						let promise = importerService.getDefaultValFields(typeSelected);
						promise.fail(
							function(){
							}
						).done(
							function(){
								let defValFields	= jQuery.parseJSON(promise.responseText);
								console.log(defValFields);

								if(defValFields && defValFields.length)
								{
									let defValSwitchButton = importerUi.createSwitch('JForm["switchDefVal"]', 'switchDefVal', 'switchDefVal', 'Use Default Values');
									jQuery("input[type='checkbox']", defValSwitchButton).prop('checked', true);

									let localDiv = jQuery("<div></div>").attr('class', 'class-def-values');
									localDiv.append(defValSwitchButton);

									defValFields.forEach(function(element){
										let htmlEle = importerUi.createDefElement(element);
										localDiv.append(htmlEle);
									});

									jQuery("#step1").append(localDiv);

									jQuery("input[type='checkbox']", defValSwitchButton).on('change', function(){
										jQuery("div.class-def-values").toggleClass(" disable-div ");
									});
								}
							}
						);
					}

					if(importerUi.initialBtnEvent == 'edit-data' || importerUi.initialBtnEvent == 'export-data'){
						let fieldDropDown	= importerUi.createDropDownList(Joomla.JText._('COM_IMP_BATCH_FIELDS_LABEL'), 'JForm["fieldList"]', '', 'fieldList', clientFieldsObject, true);
						jQuery("#step1").append(fieldDropDown);
						importerUi.registerChosen(fieldDropDown);
						
						let fetchOptionsRadios = importerUi.createRadioButtons('', "Select Option", '', 'radio-div', 'filter');
						fetchOptionsRadios.on('change',importerUi.renderRecordFetcher);
						jQuery("#step1").append(fetchOptionsRadios);
					}
					else
					{
						let submitButton = importerUi.createButton('JForm["fieldButton"]', 'fieldButton btn btn-primary', 'fieldButton', 'Submit');
						submitButton.on('click', importerUi.submitBatch);
						jQuery("#modal-footer-1").prepend(submitButton);
					}

					importerUi.doneProgress();
				}
			);
		},

	createDefElement : function(eleDetails){
		
		switch(eleDetails.type){
				case 'select' :
					let ttt = importerUi.createDropDownList(eleDetails.label, 'defFieldOptions', 'defaultValFields', eleDetails.id, eleDetails.options, false, true);
					importerUi.registerChosen(ttt);
					return ttt;
		}
	},

	renderRecordFetcher : function(){
		let radioVal = jQuery("input[type=radio]:checked", this).val();

		jQuery("#idTextAreaDiv").remove();
		jQuery(".fieldButton").remove();
		jQuery(".fetchoptions").remove();

		if (radioVal == 1)
		{
			let idTextArea = importerUi.createTextArea(Joomla.JText._('COM_IMP_BATCH_RECORD_SELECTOR_LABEL'), 'JForm["idTextArea"]', '', 'idTextArea', 'Submit');
			jQuery("#step1").append(idTextArea);			
		}
		else
		{
			importerUi.showProgress("Please wait..", 50);
			let fetchOptions = importerUi.fetchOptions();
		}

		let submitButton = importerUi.createButton('JForm["fieldButton"]', 'fieldButton btn btn-primary', 'fieldButton', 'Submit');
		submitButton.on('click', importerUi.submitBatch);
		jQuery("#modal-footer-1").prepend(submitButton);

		return true;
	},

	fetchOptions : function () {
			let promise = importerService.getFetchOptions();

			promise.fail(
				function() {
					importerUi.showErrorBox(promise);
				}
			).done(
				function() {
					let clientFetchOptions	= jQuery.parseJSON(promise.responseText);
					//console.log(clientFetchOptions);

					for(i=0 ; i < clientFetchOptions.length; i++ )
					{
						if (typeof(clientFetchOptions[i]) == 'object')
						{
							for (var key in clientFetchOptions[i])
							{
							  if (clientFetchOptions[i].hasOwnProperty(key))
							  {
								// Commented below line so that fetch-all options have preselected values.
								// let optionDropDown	= importerUi.createDropDownList(key, 'identifierOptions', 'fetchoptions', key, clientFetchOptions[i][key], false, true);
								let optionDropDown	= importerUi.createDropDownList(key, 'identifierOptions', 'fetchoptions', key, clientFetchOptions[i][key], false, false);
								jQuery("#step1").append(optionDropDown);

								importerUi.registerChosen(optionDropDown);
							  }
							}
						}
					}

					importerUi.doneProgress();
				}
			);
		},

	submitBatch : function(){
			let batchName		= jQuery("#batchName").val();
			let typeSelected	= jQuery("select#typeList").val();
			let fieldsSelected	= jQuery("select#fieldList").val();

			let finalFieldList	= fieldsSelected ? importerUi.defaultColumnList.concat(fieldsSelected).unique() : null;

			let recordsSelected = jQuery("#idTextArea").val();
			let identifierType	= jQuery("input[name=identifiers]:checked").val();

			let defValueSwitch	= jQuery("input[name=switchDefVal]:checked").val();
			var firlterOptions	= {};

			var defaultValueFields = {};

			if(defValueSwitch)
			{
				jQuery("[name='defFieldOptions']").each(function() {
					var thisObj = jQuery(this);
					if(thisObj.val().trim())
					{
						defaultValueFields[thisObj.attr('id')] = thisObj.val().trim();
					}
				});
			}

			if(importerUi.initialBtnEvent == 'add-data' && batchName.trim() == '')
			{
				swal("Error!", "Please provide batch name", "error");
				return;
			}
			else if (importerUi.initialBtnEvent == 'edit-data' || importerUi.initialBtnEvent == 'export-data')
			{
				var errorString = '';

				if (batchName.trim() == '')
				{
					errorString += "<br/>Please provide batch name";
				}

				if (identifierType == '2')
				{
					jQuery("select[name=identifierOptions]").each(function(){
						let thisId = this.id;
						if (!jQuery("option:selected", this).val())
						{
							errorString += "<br/>Please select " + thisId;
						}

						firlterOptions[thisId] = jQuery("option:selected", this).val();

					});
				}
				else if (identifierType == '1' && recordsSelected.trim() == '')
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

			var batchParams	= {
					type:typeSelected,
					batchAction : importerUi.initialBtnEvent,
					columns:jQuery.extend({}, finalFieldList)
				};

			if(Object.keys(defaultValueFields).length > 0 && defaultValueFields.constructor === Object )
			{
				batchParams.defaultVals = JSON.stringify(defaultValueFields);
			}

			if (identifierType == '2')
			{
				batchParams.fetchall = firlterOptions;
			}

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

			importerService.clientApp		= batchDetailsObj.client;
			importerService.typeSelected	= batchDetailsObj.params.type;

			var myDefValArr = [];

			if(batchDetailsObj.params.hasOwnProperty('defaultVals'))
			{
				var parsedDefVals = jQuery.parseJSON(batchDetailsObj.params.defaultVals);

				for (var key in parsedDefVals) {
				  if (parsedDefVals.hasOwnProperty(key)) {
					myDefValArr.push(key);
				  }
				}
				
			}
			console.log(myDefValArr);

			let promise = importerService.getFieldList(batchDetailsObj.params.type, batchDetailsObj.params.columns, myDefValArr);

			promise.fail(
				function() {
					//~ alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
					importerUi.showErrorBox(promise);
				}
			).done(
				function() {
					let batchColumnsObj	= jQuery.parseJSON(promise.responseText);
					importerUi.batchColumns	= batchColumnsObj;

					// For v1.1 start
					for(i=0 ; i < batchColumnsObj.length; i++ )
					{
						if (batchColumnsObj[i].primary)
						{
							importerUi.primaryKey = batchColumnsObj[i].id;
						}

						if(batchColumnsObj[i].type == 'image')
						{
							importerUi.batchImageFields[batchColumnsObj[i].id] = batchColumnsObj[i];
						}
					}
					// For v1.1 end

					importerUi.fetchall = batchDetailsObj.params.hasOwnProperty('fetchall') && (batchDetailsObj.params.fetchall != '{}');

					if(batchDetailsObj.start_id || batchDetailsObj.params.hasOwnProperty('fetchall')){
						importerUi.getRecordsClient(batchDetailsObj, batchColumnsObj);
					}
					else if(false)
					{
						importerUi.getAllRecordsClient(batchDetailsObj, batchColumnsObj);
					}else{
						importerUi.getRecordsTemp(batchDetailsObj, batchColumnsObj);
					}
				}
			);
		},
/*
	getAllRecordsClient : function (batchDetailsObj, batchColumnsObj, startPoint = 0, dumbParam1 = 0)
	{
		console.log(startPoint);
		let promise = importerService.getRecordsList(batchDetailsObj.params.type, batchColumnsObj, '', 1, startPoint, importerUi.countall);

		promise.fail(
				function() {
					importerUi.showErrorBox(promise, "getRecordsClient", batchDetailsObj, batchColumnsObj, startPoint);
				}
			).done(
				function() {
					importerUi.trialCall = 0;
					if(importerUi.countall === null || startPoint < importerUi.countall)
					{
						let batchRecordsObject	= jQuery.parseJSON(promise.responseText);
						let batchRecordsObj		= batchRecordsObject.records;
						importerUi.countall		= parseInt(batchRecordsObject.allReCount);


						let processStartTime = new Date().getTime();
						importerUi.showEstimatedRemainingTime(startPoint, importerUi.countall, processStartTime);

						if (batchRecordsObj != null)
						{
							importerUi.batchClientRecords = importerUi.batchClientRecords.concat(batchRecordsObj);
						}

						importerUi.showProgress(Joomla.JText._('COM_IMP_FETCHING_CLT_RECORDS'), ((startPoint/importerUi.countall) * 100));

						importerUi.getAllRecordsClient(batchDetailsObj, batchColumnsObj, startPoint + importerUi.fetchItemSize);
					}
					else
					{
						importerUi.loadHandsonView(batchColumnsObj, importerUi.batchClientRecords);
					}
				}
			);
	},
*/
	getRecordsClient : function(batchDetailsObj, batchColumnsObj, startPoint = 0, dumbParam1 = 0){

			// For v1.1 start
			var clientFieldsObject = [];
			for(i=0 ; i < batchColumnsObj.length; i++ )
			{
				clientFieldsObject.push(batchColumnsObj[i].id);
			}
			// For v1.1 end

			var startIdCnt = '';

			if (importerUi.fetchall)
			{
				startIdCnt = importerUi.countall;
			}
			else
			{
				var startIdStr = batchDetailsObj.start_id;
				var startIdArr = startIdStr.split(',');
				startIdCnt = startIdArr.length;
				
				var endPoint	= importerUi.fetchItemSize + startPoint;
				var slicedStartIdArr = startIdArr.slice(startPoint, endPoint);
				var slicedStartIdStr = slicedStartIdArr.join();
			}

			let processStartTime = new Date().getTime();
			importerUi.showEstimatedRemainingTime(startPoint, startIdCnt, processStartTime);

			let promise = importerService.getRecordsList(JSON.stringify(batchDetailsObj.params), batchColumnsObj, slicedStartIdStr, startPoint);

			promise.fail(
				function() {
					importerUi.showErrorBox(promise, "getRecordsClient", batchDetailsObj, batchColumnsObj, startPoint);
				}
			).done(
				function() {
					importerUi.trialCall = 0;

					if( (!importerUi.fetchall && startPoint < startIdCnt) || (importerUi.fetchall && startPoint <= importerUi.countall))
					{
						// Code to strip HTML Tags
						var regex = /(<([^>]+)>)/ig ;
						var find = '&amp;';
						var re = new RegExp(find, 'g');
						let batchStrippedObject = jQuery.parseJSON(promise.responseText.replace(regex, '').replace(re, '&'));
						let batchStrippedObj	= batchStrippedObject.records;

						let batchRecordsObject	= jQuery.parseJSON(promise.responseText);
						let batchRecordsObj		= batchRecordsObject.records;
						importerUi.countall		= parseInt(batchRecordsObject.allReCount);

						if (batchRecordsObj != null)
						{
							importerUi.batchClientRecords			= importerUi.batchClientRecords.concat(batchRecordsObj);
							importerUi.batchClientRecordsStripped	= importerUi.batchClientRecordsStripped.concat(batchStrippedObj);
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

					//console.log(batchRecordsObj.invalid);

					importerUi.batchTempRecords = importerUi.batchTempRecords.concat(batchRecordsObj.items);
					importerUi.batchTempInvalidFirst = importerUi.batchTempInvalidFirst.concat(batchRecordsObj.invalid);
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
						importerUi.batchTempInvalid = importerUi.batchTempInvalidFirst;
						importerUi.showProgress(Joomla.JText._('COM_IMP_FETCHING_TEMP_RECORDS'), 100);
						importerUi.loadHandsonView(batchColumnsObj, importerUi.batchTempRecords);
					}

					return;
				}
			);
		},

	firstRowRenderer : function (instance, td, row, col, prop, value, cellProperties){
		    Handsontable.renderers.TextRenderer.apply(this, arguments);
			td.style.background = '#e2e2e2';
			td.style.fontWeight = 'bold';
		},

	invalidRowRenderer : function (instance, td, row, col, prop, value, cellProperties){
		//console.log(instance);
		    Handsontable.renderers.TextRenderer.apply(this, arguments);

			if(importerUi.batchTempInvalid[row])
			{
				let invaFields		= JSON.parse(importerUi.batchTempInvalid[row]);
				var invalidArray	=  Object.keys(invaFields).map(function(k)
																		{
																			if (typeof(invaFields[k]) === 'string')
																			{
																				return invaFields[k];
																			}
																			else if (typeof(invaFields[k]) === 'object')
																			{
																				let chekcing = invaFields[k];
																				return Object.keys(chekcing)[0];
																			}
																		});

				td.style.background = '#CEC';

				if(invalidArray.includes(prop))
				{	
					td.style.fontWeight = 'bold';
					td.style.color = 'red';
					td.style.background = '#faa1a1';
				}
			}
		},
/*
	ajaxCallFunction : function(query='', process){

			var query = query;
			var queryVal	= query.split("|");
			var queryValue	= queryVal[queryVal.length - 1];

			var displayValue = [queryValue];

			//console.log(displayValue);

			//process([]);

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
					suggestions;

					return suggestions;
				}
			);
		},
*/
	loadHandsonView : function(batchColumnsObj, batchRecordsObj='')
	{

		var clientFieldsNames = [];
		var clientFieldsProps = [];
		var newclientFieldsNames = {};

		for(i=0 ; i < batchColumnsObj.length; i++ )
		{
			clientFieldsNames.push(batchColumnsObj[i].name);
			newclientFieldsNames[batchColumnsObj[i].id] = batchColumnsObj[i].name;

			var propObj = {'data' : batchColumnsObj[i].id};

			if(importerUi.batchDetails.params.batchAction == 'export-data')
			{
				propObj.readOnly = true;
			}
			else
			{
				propObj.readOnly = batchColumnsObj[i].readOnly;
			}

			if (batchColumnsObj[i].type == "autocomplete")
			{
				if (batchColumnsObj[i].option.length)
				{
					propObj.type	= batchColumnsObj[i].type;
					propObj.source	= batchColumnsObj[i].option;
					propObj.strict	= false;
					propObj.filter	= false;
				}
				else
				{
					propObj.editor = 'customselect';
					propObj.renderer = customSelectRenderer;
					importerUi.customRendererFields.push(batchColumnsObj[i].id);
				}
			}
			else if(batchColumnsObj[i].type == "repetablecell")
			{
				propObj.editor		= 'repHandsontableEditor';
				propObj.renderer	= repHandsontableRenderer;
				//propObj.editorFields = batchColumnsObj[i].repeatablefields;
				importerUi.repFieldDetails[batchColumnsObj[i].id] = batchColumnsObj[i].repeatablefields;
				importerUi.customRendererFields.push(batchColumnsObj[i].id);
			}
			else if (batchColumnsObj[i].type == "image")
			{
				propObj.renderer	= imageRenderer;
				importerUi.customRendererFields.push(batchColumnsObj[i].id);
			}

			clientFieldsProps.push(propObj);
		}
		// For v1.1 end

		//console.log(clientFieldsProps);

		var importBtnName = Joomla.JText._('COM_IMP_IMPORT_BTN_NAME_UPDATE');
		let handontableParams = {};
		handontableParams.rowHeaders	= true;
		handontableParams.colHeaders	= true;
		handontableParams.columns		= clientFieldsProps;
		handontableParams.contextMenu	= false;
		handontableParams.stretchH		= 'none';
		handontableParams.fixedRowsTop	= 1;

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

		importerUi.clientFieldNames = newclientFieldsNames;
		handontableParams.data = [newclientFieldsNames];

		if(batchRecordsObj)
		{
			handontableParams.data	= handontableParams.data.concat(batchRecordsObj);
		}

		handontableParams.cells = function (row, col, prop){

			//console.log(prop);
			var cellProperties = {};

			if (row === 0) {
				cellProperties.readOnly = true;
				cellProperties.renderer = importerUi.firstRowRenderer; // uses function directly
			}
			else if (importerUi.batchTempInvalid.length && importerUi.batchTempInvalid[row] && (jQuery.inArray(prop, importerUi.customRendererFields) == '-1')){
				cellProperties.renderer = importerUi.invalidRowRenderer; // uses function directly
			}

			return cellProperties;
		};

		importerUi.doneProgress("");

		//console.log(handontableParams);

		// Below two lines to load handsontable
		let container	= document.getElementById('example');
		importerUi.hot	= new Handsontable(container, handontableParams);

		// Code to update status of import-btn
		importerUi.hot.updateSettings({
			afterChange: function(changes, source) {
					//console.log(changes);
					if (changes){
						importerUi.checkTempItems = [];
						for(i = 0; i < changes.length; i++){
							if(changes[i][2] != changes[i][3]){
								document.getElementById("import-btn").disabled = true;
								document.getElementById("validate").disabled = false;
							}
						}
					}
				},
			modifyData : function(row, column, valueHolder,ioMode) {
					//console.log(row + "--- " + column + "--- " + valueHolder + "--- " + ioMode);
				},
			beforeRender : function(){
				},
			afterRender : function(){
					importerUi.doneProgress("");
				},
			afterSelection : function(r, c, r2, c2){
					importerUi.activeRow = r;
				}
		});

		//importerUi.hot.view.wt.update('beforeOnCellMouseDown', importerUi.onMyBeforeOnCellMouseDown);
		//console.log(importerUi.hot.view.wt.update);

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

		let exportButton = importerUi.createButton('JForm["btnExport"]', 'btnExport', 'btnExport', 'Export');
		exportButton.on('click', importerUi.exportData);

		let showFormButton = importerUi.createButton('JForm["btnShowForm"]', 'btnShowForm', 'btnShowForm', 'Show Form');
		showFormButton.on('click', importerFormUi.initFormView);

		let htmlSwitchButton = importerUi.createSwitch('JForm["switchHtml"]', 'switchHtml', 'switchHtml', 'Remove Html');
		jQuery("input[type='checkbox']", htmlSwitchButton).on('change', importerUi.toggleData);

		jQuery("#importer-buttons-container").append(batchDetViewBtn);

		if(importerUi.batchDetails.params.batchAction == 'export-data')
		{
			jQuery("#importer-buttons-container").append(exportButton);
			jQuery("#importer-buttons-container").append(htmlSwitchButton);
		}
		else
		{
			jQuery("#importer-buttons-container").append(showFormButton);
			jQuery("#importer-buttons-container").append(saveTempButton);
			jQuery("#importer-buttons-container").append(validateButton);
			jQuery("#importer-buttons-container").append(importButton);
		}

		jQuery("#importer-buttons-container").append(backButton);

		let filteredInvalid		= importerUi.batchTempInvalid.filter(importerUi.checkEmptyArray);
		let filteredValidated	= importerUi.validateTempItems.filter(importerUi.checkEmptyArray);

		//~ if((importerUi.batchTempInvalid.length == 1) && (filteredInvalid.length == 0) && (filteredValidated.length == 0)){
			//~ document.getElementById("import-btn").disabled = false;
			//~ document.getElementById("validate").disabled = true;
		//~ }else{
			//~ document.getElementById("import-btn").disabled = true;
			//~ document.getElementById("validate").disabled = false;
		//~ }

		if(filteredInvalid.length || filteredValidated.length || importerUi.fetchall || importerUi.batchDetails.start_id || !batchRecordsObj.length)
		{
			document.getElementById("import-btn").disabled = true;
			document.getElementById("validate").disabled = false;
		}
		else
		{
			document.getElementById("import-btn").disabled = false;
			document.getElementById("validate").disabled = true;
		}

		delete importerUi.batchTempRecords;
		delete importerUi.batchClientRecords;
	},

	exportData : function()
	{
		//console.log("inside function");
		  var exportPlugin = importerUi.hot.getPlugin('exportFile');
		  exportPlugin.downloadFile('csv', {filename: importerUi.batchDetails.batch_name});
	},

	viewBaatchStatus : function ()
	{
		importerUi.showProgress('Fetching batch details.. Please wait..');
		let tableRecordsCount	= importerUi.hot.countRows() - importerUi.hot.countEmptyRows() - 1;

		let promise = importerService.getTempStatus(importerUi.batchDetails.id);
			promise.fail(
				function() {
					//~ alert(Joomla.JText._('COM_IMP_ERROR_MSG'));
					importerUi.showErrorBox(promise);
				}
			).done(
				function() {
					importerUi.doneProgress();
					let temDetails	= jQuery.parseJSON(promise.responseText);
					jQuery('#batchStatus').modal('show');
					jQuery('#batchStatusTitle').text(importerUi.batchDetails.batch_name);
					jQuery('#batchStatusBody').append("<ul class='list-group'></ul>");
					jQuery('#batchStatusBody ul').append("<li class='list-group-item'>" + Joomla.JText._('COM_IMP_TOT_CSV_REC') + "<b>" + tableRecordsCount + "</b></li>");
					jQuery('#batchStatusBody ul').append("<li class='list-group-item'>" + Joomla.JText._('COM_IMP_TOT_TMP_REC') + "<b>" + temDetails.itemsTotal + "</b></li>");
					jQuery('#batchStatusBody ul').append("<li class='list-group-item'>" + Joomla.JText._('COM_IMP_TOT_VLD_REC') + "<b>" + temDetails.validatedTotal + "</b></li>");
					jQuery('#batchStatusBody ul').append("<li class='list-group-item'>" + Joomla.JText._('COM_IMP_TOT_INVLD_REC') + "<b>" + temDetails.invalidTotal + "</b></li>");
					jQuery('#batchStatusBody ul').append("<li class='list-group-item'>" + Joomla.JText._('COM_IMP_TOT_IMP_REC') + "<b>" + temDetails.importedTotal + "</b></li>");

					var divDefaultShowCover = jQuery("<div></div>");
					if(importerUi.batchDetails.params.hasOwnProperty('defaultVals'))
					{
						divDefaultShowCover.attr("class", "defaultDivCover");
						defValParsed	= JSON.parse(importerUi.batchDetails.params.defaultVals);

						divDefaultShowCover.append(jQuery("<span></span>"));

						for (var key in defValParsed)
						{
							let divDefaultShow = jQuery("<div></div>").text(key + " = " + defValParsed[key]);
							divDefaultShowCover.append(divDefaultShow);
						}

						jQuery('#batchStatusBody').append(divDefaultShowCover);
					}
					
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
			let textToShow = showPercent ? text + " " + showPercent + "% done" : text;

			jQuery("#progress-text #progress-text-span").text(textToShow);
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
			jQuery("#progress-text").removeClass("text-show").addClass("text-hide");
			jQuery("#progress-text #progress-time-span").text("");
		},

	importTempRecords : function (event, itemStart=0, dumbParam1 = 0, dumbParam2 = 0){
			var processingStatusText	= Joomla.JText._('COM_IMP_REC_UPDATING');
			var successStatusText		= Joomla.JText._('COM_IMP_REC_UPDATED');
			let allItems				= importerUi.hot.getSourceData();

			if (itemStart == 0)
			{
				importerUi.shiftedRow = [allItems.shift()];
			}

			let recordsCount			= (allItems).length;
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
					importerUi.hot.loadData(importerUi.shiftedRow.concat(importerUi.hot.getSourceData()));
					importerUi.hot.render();
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
						importerUi.batchTempInvalid = [''];
					}

					importerUi.batchTempInvalid = importerUi.batchTempInvalid.concat(chekcingArray);

					// Assigning temp table id's to handsontable data
					for (i = 0; i < importedRecDetails.length; i++){
						if (importedRecDetails[i][importerUi.primaryKey] !== null){
							//importerUi.hot.getSourceData()[itemStart + i][importerUi.primaryKey] = importedRecDetails[i][importerUi.primaryKey];
							//~ var newAllItems = importerUi.hot.getSourceData();
							//~ newAllItems.shift();
							importerUi.hot.getSourceData()[itemStart + i][importerUi.primaryKey] = importedRecDetails[i][importerUi.primaryKey];
						}
					}

					importerUi.updateTempRecordsAfterImport(event, itemStart, importedRecDetails, importedInvalidDetails);
				}
			);
		},

	toggleData : function(event,state){

			event.preventDefault();
			var toggleDataArr	= [];
			toggleDataArr		= [importerUi.clientFieldNames];

			importerUi.showProgress(Joomla.JText._('HTML Toggle'), 50);

			if(jQuery(this).is(":checked"))
			{
				toggleDataArr = toggleDataArr.concat(importerUi.batchClientRecordsStripped);
			}
			else
			{
				toggleDataArr = toggleDataArr.concat(importerUi.batchClientRecords);
			}
			
			importerUi.hot.loadData(toggleDataArr);
			importerUi.hot.render();
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
			
			if(itemStart == 0)
			{
				importerUi.shiftedRow = [allItems.shift()];
			}
			
			//console.log("chekcing element count  == " + allItems.length);



			let recordsCount	= allItems.length;
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

							var itemIndex = parseInt(itemStart) + parseInt(i);

							//console.log(itemIndex + " tempid " + tempIds[i] + " itemidid " + importerUi.hot.getSourceData()[itemIndex].zooid);
							
							if (importerUi.hot.getSourceData()[itemIndex])
							{
								importerUi.hot.getSourceData()[itemIndex].tempId = tempIds[i];
							}
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
			
			//console.log("chekicng for validate item lenthg " + allItems.length);
			
			let recordsCount	= allItems.length;
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
						importerUi.batchTempInvalid = [''];
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
					importerUi.hot.loadData(importerUi.shiftedRow.concat(importerUi.hot.getSourceData()));
					//importerUi.copyHeader = 1;
					importerUi.hot.render();

					//location.reload();
					let filteredInvalid = importerUi.batchTempInvalid.filter(importerUi.checkEmptyArray);

					if(importerUi.batchTempInvalid.length && filteredInvalid.length == 0 && event == 'validate'){
						document.getElementById("import-btn").disabled = false;
						document.getElementById("validate").disabled = true;
					}else{
						document.getElementById("import-btn").disabled = true;
						document.getElementById("validate").disabled = false;
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

			$comboEle = jQuery("<select></select>").attr("id", id).attr('name', name).attr("class", "span5 select-box-for-chosen");
			if(multiplee){
				$comboEle.attr("multiple", "multiple");
			}

			if(defaultOption)
			{
				$comboEle.append("<option value=''></option>");
			}

			jQuery.each(optionList, function (i, el) {
				if(typeof(el) == 'object'){
					el = el.name;
				}
				$comboEle.append("<option value=" + i + ">" + el + "</option>");
			});

			//jQuery($comboEle).chosen();
		
			$comboLabel.appendTo($combo);
			$comboEle.appendTo($combo);

			return $combo;
		},

	createRadioButtons : function (options = '', label, name, classs, id)
	{
		let $comboLabel = '';
		let $comboEle = '';
		let $combo = '';
		
		options = [{name:"identifiers", value:"1", text:"Provide Aliases"}, {name:"identifiers", value:"2", text:"Fetch All"}]; 

		$combo = jQuery("<div></div>").attr("class", classs).attr("id", id+"Div");

		if(label){
				$comboLabel = jQuery("<label></label>").attr("for", name).attr("class", "span2").text(label);
			}

		$comboLabel.appendTo($combo);

		for (i = 0; i < options.length; i++)
		{
			$comboEle = importerUi.createRadios(options[i].name, options[i].value, options[i].text);
			$comboEle.appendTo($combo);
		}

		return $combo;
	},

	createRadios : function (name, value, text)
	{
		let readySpan	= '';
		let readyDiv	= '';
		let readyRadio	= '';
		
		readyDiv	= jQuery("<span></span>");
		readySpan	= jQuery("<label></label>").text(text).attr('for', name + '-' + value).attr('class', 'radio-label');
		readyRadio = jQuery("<input></input>").attr("type", "radio").attr("name", name).attr("value", value).attr('id', name + '-' + value);
		
		readyRadio.appendTo(readyDiv);
		readySpan.appendTo(readyDiv);
		return readyDiv;
	},

	createSwitch : function (classs, name, value, text)
	{
		var mainCoverDiv	= '';
		var messDiv		= '';
		var readyLabel		= '';
		var readyCheckBox	= '';

		mainCoverDiv	= jQuery("<div></dvi>").attr('class', 'switch-main-cover');
		messDiv			= jQuery("<div></dvi>").attr('class', 'switch-cover').text(text);
		readyLabel		= jQuery("<label></label>").attr('class', 'switch');
		readyCheckBox	= jQuery("<input></input>").attr("type", "checkbox").attr("name", name).attr('id', name );
		var sliderDiv	= jQuery("<div></div>").attr("class", "slider round");

		readyCheckBox.appendTo(readyLabel);
		sliderDiv.appendTo(readyLabel);
		messDiv.appendTo(mainCoverDiv);
		readyLabel.appendTo(mainCoverDiv);
		
		return mainCoverDiv;
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
			importerUi.showProgress('Preparing CSV', 1);

			importerUi.postItemSize = importerUi.fetchItemSize = parseInt(jQuery("#pfSize").val());

			importerUi.batchId = batchId;
			importerUi.getBatch();
		}
		
		//~ jQuery(".toggleHtml").change(function(){
				//~ importerUi.toggleData(jQuery(this).is(":checked"));
			//~ })
	});

Array.prototype.unique = function() {
    var a = this.concat();
    for(var i=0; i<a.length; ++i) {
        for(var j=i+1; j<a.length; ++j) {
            if(a[i] === a[j])
                a.splice(j--, 1);
        }
    }

    return a;
};
