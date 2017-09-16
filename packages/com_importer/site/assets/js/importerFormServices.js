importerFormServices = {

	getColumns : function() {
		return importerUi.batchColumns;
	},

	getActiveRow : function() {
		return importerUi.activeRow;
	},
	
	getActiveRowData : function(activeRow) {
		return importerUi.hot.getSourceDataAtRow(activeRow);
	},
	
	getFormDataObject : function(){	
		var serForObj = jQuery("#bulktool-form-original form").serializeArray().reduce(function(obj, item) {
							obj[item.name] = item.value;
							return obj;
						}, {});

		return serForObj;
	},

	getFormHiddenObject : function(){
		var serForObj = jQuery("#bulktool-form-hidden form").serializeArray().reduce(function(obj, item) {
							obj[item.name] = item.value;
							return obj;
						}, {});

		return serForObj;
	},

	getInvalidFields : function(curRow){
		var invalidData = importerUi.batchTempInvalid[curRow] ? JSON.parse(importerUi.batchTempInvalid[curRow]) : '';

		var result = invalidData ? Object.keys(invalidData).map(function (key) { return invalidData[key]; }) : "";

		return result;
	},

	getTotalRows : function(){
		return importerUi.hot.countRows() - 1; 
	},

	isDataUpdated : function(row){

		var rowData		= importerFormServices.getFormHiddenObject();
		var formData	= importerFormServices.getFormDataObject();

		for (var key in formData)
		{
			if (formData.hasOwnProperty(key))
			{
				if(rowData[key] === undefined)
				{
					rowData[key] = '';
				}

				if(formData[key] != rowData[key])
				{
					return true;
				}
			}
		}

		return false;
	},

	decodeHtml : function(html) {
		var txt = jQuery("<textarea></textarea>").text(html);
		return txt.text();
	},

	setActiveRow : function(activeRow){
		importerUi.hot.selectCell(activeRow, 1);
		return true;
	},

	setDataAtRow : function(data, row){
		for (var key in data) {
			if (data.hasOwnProperty(key)) {
				importerUi.hot.setDataAtRowProp(row, key, data[key]);
			}
		}

		return true;
	},
	
	getSuggestions : function(fieldVal, fieldId){
		return importerService.getSuggestions(fieldVal, fieldId);
	}

}
