// literal pattern

var importerUi = {

	batchId : '',
	batchColumns : '',
	batchDetails : '',
	colFields : '',
	colProperties : '',
	batchTempRecords : [],
	batchTempInvalid : [],
	colName : '',
	hot : '',
	postItemSize : 40,

	step1 : function()
	{
		importerService.clientApp = jQuery("#clientApp").val();

		let batchNameBox = importerUi.createTextbox("Batch Name : ", 'JForm[batchName]', '', 'batchName', '')
		jQuery("#step1").append(batchNameBox);

		let promise = importerService.getTypeList();

		promise.fail(
			function() {
				alert('somethig went wrong');
			}
		).done(
			function() {
				let clientTypesObj	= jQuery.parseJSON(promise.responseText);

				if(Object.keys(clientTypesObj).length > 1)
				{
					let typeDropDown	= importerUi.createDropDownList('Types : ', 'JForm["typeList"]', '', 'typeList', clientTypesObj, false);

					typeDropDown.on('change', importerUi.appendFieldList);
					jQuery("#step1").append(typeDropDown);
				}
				else
				{
					importerUi.appendFieldList('', Object.keys(clientTypesObj)[0]);
				}
			}
		);
	},

	appendFieldList : function (event, singleType='')
		{
			let typeSelected	= (event) ? jQuery("option:selected", this).val() : singleType;
			let promise			= importerService.getFieldList(typeSelected);

			promise.fail(
				function() {
					alert('somethig went wrong');
				}
			).done(
				function() {
					let clientFieldsObj	= jQuery.parseJSON(promise.responseText);
					let fieldDropDown	= importerUi.createDropDownList('Fields : ', 'JForm["fieldList"]', '', 'fieldList', clientFieldsObj.colFields, true);

					jQuery("#fieldListDiv").remove();
					jQuery(".fieldButton").remove();
					jQuery("#idTextAreaDiv").remove();
					jQuery("#step1").append(fieldDropDown);

					let submitButton = importerUi.createButton('JForm["fieldButton"]', 'fieldButton', 'fieldButton', 'Submit');
					submitButton.on('click', importerUi.submitBatch);

					let idTextArea = importerUi.createTextArea("Record id's", 'JForm["idTextArea"]', '', 'idTextArea', 'Submit');
					jQuery("#step1").append(fieldDropDown);
					jQuery("#step1").append(idTextArea);
					jQuery("#step1").append(submitButton);

				}
			);
		},

	submitBatch : function(){

			let batchName = jQuery("#batchName").val();
			let typeSelected = jQuery("select#typeList").val();
			let fieldsSelected = jQuery("select#fieldList").val();
			let recordsSelected = jQuery("#idTextArea").val();

			let batchParams	= {
					batchName:batchName,
					type:typeSelected, 
					columns:jQuery.extend({}, fieldsSelected)
				};
            
			let promise = importerService.saveBatch(batchParams, recordsSelected);

			promise.fail(
				function() {
					alert("something went wrong!")
				}
			).done(
				function() {
					let clientFieldsObj	= jQuery.parseJSON(promise.responseText);
					window.location = "index.php?option=com_importer&view=importer&layout=handson&batch_id=" + clientFieldsObj;
				}
			);

		},

	getBatch : function ()
		{
			let batchDetailsPromise = importerService.getBatch(this.batchId);
			batchDetailsPromise.fail(
				function() {
					alert("something went wrong!")
				}
			).done(
				function() {
					let batchDetailsObj	= jQuery.parseJSON(batchDetailsPromise.responseText);
					let getColumns		= importerUi.getColumns(batchDetailsObj);
					importerUi.batchDetails = batchDetailsObj;
				}
			);
		},

	getColumns : function(batchDetailsObj)
		{
			importerService.clientApp = batchDetailsObj.client;
			importerService.typeSelected = batchDetailsObj.params.type;
			let promise = importerService.getFieldList(batchDetailsObj.params.type, batchDetailsObj.params.columns);
			promise.fail(
				function() {
					alert("something went wrong!")
				}
			).done(
				function() {
					let batchColumnsObj	= jQuery.parseJSON(promise.responseText);
					this.batchColumns	= batchColumnsObj;

					if(batchDetailsObj.start_id)
					{
						importerUi.getRecordsClient(batchDetailsObj, batchColumnsObj);
					}
					else
					{
						importerUi.getRecordsTemp(batchDetailsObj, batchColumnsObj);
					}
				}
			);
		},

	getRecordsClient : function(batchDetailsObj, batchColumnsObj){

			let promise = importerService.getRecordsList(batchDetailsObj.params.type, batchColumnsObj.colIds, batchDetailsObj.start_id);
			promise.fail(
				function() {
					alert("something went wrong!")
				}
			).done(
				function() {
					let batchRecordsObj	= jQuery.parseJSON(promise.responseText);
					importerUi.loadHandsonView(batchColumnsObj, batchRecordsObj);
				}
			);			
		},

	getRecordsTemp : function(batchDetailsObj, batchColumnsObj, tempOffset = 0){

			let promise = importerService.getRecordsTemp(batchDetailsObj.id, tempOffset);
			promise.fail(
				function() {
					alert("something went wrong!")
				}
			).done(
				function() {
					let batchRecordsObj	= jQuery.parseJSON(promise.responseText);

					importerUi.batchTempRecords = importerUi.batchTempRecords.concat(batchRecordsObj.items);
					importerUi.batchTempInvalid = importerUi.batchTempInvalid.concat(batchRecordsObj.invalid);

					if(importerUi.batchTempRecords.length <  batchRecordsObj.count)
					{
						importerUi.getRecordsTemp(batchDetailsObj, batchColumnsObj, importerUi.batchTempRecords.length);
					}
					else
					{
						importerUi.loadHandsonView(batchColumnsObj, importerUi.batchTempRecords);
					}

					return;
				}
			);
		},

	invalidRowRenderer : function (instance, td, row, col, prop, value, cellProperties)
		{
		    Handsontable.renderers.TextRenderer.apply(this, arguments);

		    if(prop == "title")
		    {
		    	td.style.fontWeight = 'bold';
			    td.style.color = 'red';
		    }

		    td.style.background = '#CEC';
		},

	loadHandsonView : function(batchColumnsObj, batchRecordsObj='')
		{
			let handontableParams = {};
			handontableParams.rowHeaders	= true;
			handontableParams.colHeaders	= batchColumnsObj.colName;

			handontableParams.columns		= batchColumnsObj.colProperties;
			handontableParams.contextMenu	= true;
			handontableParams.stretchH		= 'none';
			handontableParams.minSpareRows	= 1;

			if(batchRecordsObj)
			{
				handontableParams.data	= batchRecordsObj;
			}

			handontableParams.cells = function (row, col, prop)
			{
				var cellProperties = {};
				if (importerUi.batchTempInvalid.length && importerUi.batchTempInvalid[row] !== null)
				{
					console.log("row = " + row + " objlength = " + batchRecordsObj.length);
					cellProperties.renderer = importerUi.invalidRowRenderer; // uses function directly
				}

				return cellProperties;
			};

			// Below two lines to load handsontable
			let container	= document.getElementById('example');
			importerUi.hot	= new Handsontable(container, handontableParams);

			importerUi.hot.updateSettings({
					afterChange: function(changes, source) {
							console.log("changed");
						}
					});

			let validateButton = importerUi.createButton('JForm["validate"]', 'validate', 'validate', 'Validate');
			validateButton.on('click', importerUi.saveTempRecords);

			let saveTempButton = importerUi.createButton('JForm["saveTemp"]', 'saveTemp', 'saveTemp', 'Save Progress');
			saveTempButton.on('click', importerUi.saveTempRecords);

			let backButton = importerUi.createButton('JForm["back"]', 'back pull-right', 'back', 'Cancel');
			backButton.on('click', importerUi.goFirst);

			jQuery("#importer-buttons-container").append(saveTempButton);
			jQuery("#importer-buttons-container").append(validateButton);
			jQuery("#importer-buttons-container").append(backButton);

		},

	goFirst : function()
		{
			window.location = "index.php?option=com_importer&view=importer&clientapp=" + importerUi.batchDetails.client;
		},

	saveTempRecords : function(event, itemStart=0)
		{
			let allItems		= importerUi.hot.getSourceData();
			let recordsCount	= (importerUi.hot.getSourceData()).length;
			let pgWidth			= ((itemStart + 1) / recordsCount)*(100);
			let itemsEnd		= importerUi.postItemSize + itemStart;

			jQuery("#pg-bar").css('width', pgWidth + "%");

			let checkItems	= allItems.slice(itemStart, itemsEnd);
			let promise		= importerService.saveTempRecords(checkItems, importerUi.batchDetails);

			promise.fail(
				function() {
					alert("something went wrong!")
				}
			).done(
				function() {

					let tempIds	= jQuery.parseJSON(promise.responseText);

					// Assigning temp table id's to handsontable data
					for (i = 0; i < tempIds.length; i++)
					{
						if(tempIds[i] !== null)
						{
							importerUi.hot.getSourceData()[itemStart + i].tempId = tempIds[i];
							console.log("temp ids - " + tempIds[i]);
						}
					}

					if (itemStart >= recordsCount)
					{
						alert("saved in temp");
						jQuery("#pg-bar").css('width', "0%");
						console.log(importerUi.hot.getSourceData());

						importerUi.updateBatch();
					}
					else
					{
						if(event.target.id === 'validate')
						{
							importerUi.validateTempRecords(event, itemStart);
						}						
						else
						{
							importerUi.saveTempRecords(event, (itemStart + importerUi.postItemSize));
						}
					}
				}
			);

			return;			
		},

	validateTempRecords : function(event, itemStart)
		{
			let allItems		= importerUi.hot.getSourceData();
			let recordsCount	= (importerUi.hot.getSourceData()).length;
			let itemsEnd		= importerUi.postItemSize + itemStart;
			let pgWidth			= ((itemStart + itemsEnd) / (3*recordsCount))*(100);
			let checkItems		= allItems.slice(itemStart, itemsEnd);

			jQuery("#pg-bar").css('width', pgWidth + "%");

			let promise = importerService.validateRecords(checkItems, importerUi.batchDetails);

			promise.fail(
				function() {
					alert("something went wrong!")
				}
			).done(
				function() {
				
					let invalidRec	= jQuery.parseJSON(promise.responseText);
					
					if(invalidRec.length)
					{
					}
					else
					{
						importerUi.saveTempRecords(event, (itemStart + importerUi.postItemSize));
					}
				}
			);

		},

	updateTempRecords : function(event, itemStart, invalidData)
		{
			
		},

	updateBatch : function()
		{
			let promise = importerService.updateBatch(importerUi.batchDetails);
			promise.fail(
				function() {
					alert("something went wrong!")
				}
			).done(
				function() {
					
				}
			);
		},

	createTextbox : function (label='', name, classs, id, placeholder='')
		{
			let $comboLabel = '';
			let $comboEle = '';
			let $combo = '';

			$combo = jQuery("<div></div>").attr("class", classs).attr("id", id+"Div");

			if(label){
				$comboLabel = jQuery("<label></label>").attr("for", name).attr("class", "span2").text(label);
			}

			$comboEle = jQuery("<input></input>").attr("type", "text").attr("id", id).attr('name', name).attr("class", "span5").attr('placeholder', placeholder);
			$comboLabel.appendTo($combo);
			$comboEle.appendTo($combo);

			return $combo;
		},

	createDropDownList : function (label='', name, classs, id, optionList, multiplee=false)
		{
			let $comboLabel = '';
			let $comboEle = '';
			let $combo = '';

			$combo = jQuery("<div></div>").attr("class", classs).attr("id", id+"Div");

			if(label)
			{
				$comboLabel = jQuery("<label></label>").attr("for", name).attr("class", "span2").text(label);
			}

			$comboEle = jQuery("<select></select>").attr("id", id).attr('name', name).attr("class", "span6");
			if(multiplee){
				$comboEle.attr("multiple", "multiple");
			}
			$comboEle.append("<option>Select</option>");

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

	createButton : function (name, classs, id, textDisplay)
		{
			let combo = jQuery("<button></button>").attr("id", id).attr("class", classs).attr('name', name).text(textDisplay);

			return combo;
		},

	createTextArea : function (label, name, classs, id, placeholder)
		{
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

		if(batchId)
		{
			importerUi.batchId = batchId;
			importerUi.getBatch();
		}
		else
		{
			importerUi.step1();
		}
	});
