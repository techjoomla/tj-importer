var bioInvalidFields;

var negativeValueRenderer = function(instance, td, row, col, prop, value, cellProperties){

	var currencyCode = value;

	while (td.firstChild) {
		td.removeChild(td.firstChild);
	}

	if(bioInvalidFields)
	{
		if (row in bioInvalidFields)
		{
			td.style.background = '#CEC';

			if (bioInvalidFields[row].includes(prop))
			{
				td.style.color = 'red';
				td.style.fontWeight = 'bold';
				td.style.background = '#faa1a1';
			}
		}
	}

	if (currencyCode) {
		var flagElement = document.createElement('DIV');
		flagElement.className = 'setMaxWidth';
		flagElement.innerHTML = currencyCode;
		td.appendChild(flagElement);
	} else {
		var textNode = document.createTextNode(value === null ? '' : value);
		td.appendChild(textNode);
	}
}

var repHandsontableRenderer = function(instance, td, row, col, prop, value, cellProperties) {

	var iconElement = document.createElement('DIV');
	iconElement.className = 'htAutocompleteArrow';
	iconElement.appendChild(document.createTextNode("Edit"));

    while (td.firstChild) {
      td.removeChild(td.firstChild);
    }

	var flagElement = document.createElement('DIV');
	flagElement.className = 'repHandsontableVal';

	var spanElement = document.createElement('span');
	spanElement.innerHTML = value;

	flagElement.appendChild(spanElement);

	flagElement.appendChild(iconElement);

	td.appendChild(flagElement);

	if (importerUi.batchTempInvalid.length && importerUi.batchTempInvalid[row])
	{
		td.style.background = '#CEC';

		let invaFields		= JSON.parse(importerUi.batchTempInvalid[row]);
		var invalidArray	=  Object.keys(invaFields).map(function(k)
															{
																if (typeof(invaFields[k]) === 'object')
																{
																	//bioInvalidFields.push(invaFields[k]);
																	let chekcing = invaFields[k];
																	//console.log(Object.keys(chekcing));
																	return Object.keys(chekcing)[0];
																	//return 'chekcing';
																}
															});

		if (invalidArray.includes(prop))
		{
			td.style.color = 'red';
			td.style.fontWeight = 'bold';
			td.style.background = '#faa1a1';
		}
	}
}

// Extend the Autocomplete editor
var repHandsontable = Handsontable.editors.TextEditor.prototype.extend();

repHandsontable.prototype.createElements = function() {
  Handsontable.editors.TextEditor.prototype.createElements.apply(this, arguments);

  var DIV = document.createElement('DIV');
  DIV.className = 'handsontableEditor democlassaname';
  this.TEXTAREA_PARENT.appendChild(DIV);

  this.htContainer = DIV;
  //this.assignHooks();
};

repHandsontable.prototype.prepare = function(td, row, col, prop, value, cellProperties) {

	Handsontable.editors.TextEditor.prototype.prepare.apply(this, arguments);

	var parent = this;
	var repFieldCols = [];
	var repFieldColsProp = [];
	var repFieldColsDetials = importerUi.repFieldDetails[cellProperties.prop];
	var parsedValue	= [];

	if (value)
	{
		parsedValue = jQuery.parseJSON(value);
	}

	for(i=0 ; i < repFieldColsDetials.length; i++ )
	{
		var propObj = {'data' : repFieldColsDetials[i].id, 'renderer': negativeValueRenderer};
		repFieldCols.push(repFieldColsDetials[i].name);
		repFieldColsProp.push(propObj);
	}

	var options = {
		colHeaders: repFieldCols,
		rowHeaders: true,
		columns : repFieldColsProp,
		minSpareRows:1,
		data : parsedValue
	};

	if (this.cellProperties.handsontable) {
		extend(options, cellProperties.handsontable);
	}
	
	this.htOptions = options;
}

repHandsontable.prototype.open = function() {

	Handsontable.editors.TextEditor.prototype.open.apply(this, arguments);

	var that = this;
	var i	= 0;

	jQuery("#modal-title-2").text(importerUi.hot.getDataAtCell(that.row, 0) + " - " + importerUi.hot.getDataAtCell(that.row, 1));

	if (that.htEditor) {
		that.htEditor.destroy();
	}

	if (importerUi.batchTempInvalid.length && importerUi.batchTempInvalid[that.row]){
		let invaFields		= JSON.parse(importerUi.batchTempInvalid[that.row]);
		var invalidArray	=  Object.keys(invaFields).map(function(k)
															{
																if (typeof(invaFields[k]) === 'object')
																{
																	let chekcing = invaFields[k];
																	var tempInvalidFormat = {};

																	tempInvalidFormat.columnId = Object.keys(chekcing)[0];
																	tempInvalidFormat.columnInvalidData = chekcing[Object.keys(chekcing)[0]];

																	bioInvalidFields = chekcing[Object.keys(chekcing)[0]];
																}
															});
	}
	else{
		bioInvalidFields = '';
	}

	jQuery('#repHandsontable').on('shown', function()
	{
		if(i === 0)
		{
			let container	= document.getElementById('win');
			that.htEditor	= new Handsontable(container, that.htOptions);

			jQuery('#handsontableCLose').click({that:that}, closeRepHandson);
			jQuery('#repHandsontableSave').click({that:that}, saveRepHandson);

			i++;
		}
    });

	jQuery('#repHandsontable').modal('show');
};

var closeRepHandson = function(event)
{
	event.preventDefault();
	//~ event.data.that.htEditor.destroyEditor();
	jQuery('#repHandsontable').modal('hide');
}

var saveRepHandson = function(event)
{
	var gridData = event.data.that.htEditor.getSourceData();
	var cleanedGridData = [];

	jQuery.each( gridData, function( rowKey, object) {
		if (!event.data.that.htEditor.isEmptyRow(rowKey)) cleanedGridData.push(object);
	});

	let row			= event.data.that.row;
	let col			= event.data.that.col;

	var cleanedJSON = cleanedGridData.length ? JSON.stringify(cleanedGridData) : '';

	event.data.that.instance.setDataAtCell(row, col, cleanedJSON, 'updateRepData');
	//~ event.data.that.htEditor.destroyEditor();
	jQuery('#repHandsontable').modal('hide');
}

// Register cutomAutoCompleteEditor as customselect.
Handsontable.editors.registerEditor('repHandsontableEditor', repHandsontable);
