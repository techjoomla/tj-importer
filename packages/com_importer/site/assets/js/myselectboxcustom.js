var customOrignalValue = [];
var choicesLength;
// Extend the Autocomplete editor
var customAutocompleteEditor = Handsontable.editors.AutocompleteEditor.prototype.extend();

// To set sugesstion-ajax-call global so that can be aborted on any of a particular event
var $ajaxCall;

// To array_filter the javascript array
var notEmpty = function(value)
{
	return value != '';
}

// This is used to cancle the selection by clicking on cancel icon
var callBackToCancel = function (event)
{
	var value		= event.data.value;
	let row			= event.data.row;
	let col			= event.data.col;
	let deleteNode	= jQuery(this).attr('data-id');
	var updatedVal	= '';

	var splitVal = value.split("|");

	if (deleteNode > -1)
	{
		splitVal.splice(deleteNode, 1);
	}

	var finalSplitVal	= splitVal.filter(notEmpty);

	if (finalSplitVal.length)
	{
		updatedVal		= finalSplitVal.join('|') + "|";
	}

	event.data.instance.setDataAtCell(row, col, updatedVal, 'unselect');
};

/* 
 * This is the custom renderer
 * Mainly chosen classes are used here so that the look of ripro values is like chosen
 * and cancel icon is provided
 */
var customSelectRenderer = function(instance, td, row, col, prop, value, cellProperties) {
	var iconElement = document.createElement('DIV');
	iconElement.className = 'htAutocompleteArrow';
	iconElement.appendChild(document.createTextNode("▼"));

    while (td.firstChild) {
      td.removeChild(td.firstChild);
    }

   if (value) {
		var splitVal = value.split("|");
		var splitLen = splitVal.length;

		var flagElement = document.createElement('DIV');
		flagElement.className = 'chosen-container chosen-container-multi';

		var ulElement = document.createElement('ul');
		ulElement.className = 'chosen-choices';

		if (importerUi.batchTempInvalid.length && importerUi.batchTempInvalid[row])
		{
			ulElement.className = 'chosen-choices chosen-invalid-choice';
		}

		for (var i = 0; i < splitLen; i++)
		{
			if (splitVal[i])
			{
				var liElement = document.createElement('li');
				liElement.className = 'search-choice';

				if (importerUi.batchTempInvalid.length && importerUi.batchTempInvalid[row])
				{
					let invaFields		= JSON.parse(importerUi.batchTempInvalid[row]);
					var invalidArray	=  Object.keys(invaFields).map(function(k) { return invaFields[k] });
					
					if (invalidArray.includes(prop))
					{
						liElement.className = 'search-choice invalid-choice';
					}
				}

				var spanElement = document.createElement('span');
				spanElement.innerHTML = splitVal[i];

				var anchorElement = document.createElement('a');
				anchorElement.className = 'search-choice-close';
				anchorElement.setAttribute('data-id', i);

				if (!cellProperties.readOnly)
				{
					
				}

				liElement.appendChild(spanElement);
				
				if (!cellProperties.readOnly)
				{
					jQuery(anchorElement).on('click', {instance: instance, row: row, col: col, value: value},  callBackToCancel);
					liElement.appendChild(anchorElement);
				}
				
				ulElement.appendChild(liElement);
			}
		}

		flagElement.appendChild(ulElement);
		flagElement.appendChild(iconElement);

		td.appendChild(flagElement);
		
		if (importerUi.batchTempInvalid.length && importerUi.batchTempInvalid[row])
		{
			td.style.background = '#CEC';
		}
    }
    else{
		var textNode = document.createTextNode(value === null ? '' : value);
		td.appendChild(textNode);
		td.appendChild(iconElement);

		if (importerUi.batchTempInvalid.length && importerUi.batchTempInvalid[row])
		{
			td.style.background = '#CEC';
		}
    }
  };

// Initialise the Custom editor
customAutocompleteEditor.prototype.init = function() {
  Handsontable.editors.HandsontableEditor.prototype.init.apply(this, arguments);
  this.query = null;
  this.choices = [];
};

// Function to give class name as customAutocompleteEditor
customAutocompleteEditor.prototype.createElements = function() {
  Handsontable.editors.AutocompleteEditor.prototype.createElements.apply(this, arguments);
  addClass(this.htContainer, 'AutocompleteEditor customAutocompleteEditor');
  addClass(this.htContainer, window.navigator.platform.indexOf('Mac') === -1 ? '' : 'htMacScroll');
};

// Function to set beforeOnCellMouseDown event to select alias of displayed suggestion
customAutocompleteEditor.prototype.prepare= function(td, row, col, prop, value, cellProperties) {
  Handsontable.editors.AutocompleteEditor.prototype.prepare.apply(this, arguments);

  var parent = this;

  var options = {
    startRows: 0,
    startCols: 0,
    minRows: 0,
    minCols: 0,
    className: 'listbox customListBox',
    copyPaste: false,
    autoColumnSize: false,
    autoRowSize: false,
    readOnly: true,
    fillHandle: false,
    preventOverflow: 'horizontal',
    afterOnCellMouseDown: function() {

      var value = this.getValue();
      // if the value is undefined then it means we don't want to set the value
      if (value !== void 0) {
        parent.setValue(value);
      }
      parent.instance.destroyEditor();
    },
    beforeOnCellMouseDown: function(event, coords, TD, blockCalculations) {
		coords.col = 2;
    }
  };

  if (this.cellProperties.handsontable) {
    extend(options, cellProperties.handsontable);
  }
  this.htOptions = options;
};

// Function to get suggestions from ajax call
customAutocompleteEditor.prototype.queryChoices = function(query) {

	var that = this;
	//var query = query;
	var queryVal	=  query.split("|");
	var queryValue	= queryVal[queryVal.length - 1];

	queryVal.pop();
	customOrignalValue = queryVal.join("|");

	this.query = queryValue;

	if($ajaxCall && $ajaxCall.readyState != 4){
		$ajaxCall.abort();
	}

	let serchText = "Searching for '" + queryValue +  "'";
	that.updateChoicesList([[serchText]]);

	// Call to services get suggestion method
	$ajaxCall = importerService.getSuggestions(queryValue.trim(), this.prop);

	$ajaxCall.fail(
		function() {
			importerUi.showErrorBox();
		}
	).done(
		function() {
			var suggestions	= jQuery.parseJSON($ajaxCall.responseText);
			let noSerchText = "No suggestion for '" + queryValue +  "'";

			if(!suggestions.length)
			{
				suggestions = [[noSerchText]];
			}

			that.updateChoicesList(suggestions);
			return;
		}
	);
};

// Not Same as core funtion
customAutocompleteEditor.prototype.updateChoicesList = function(choices) {
	var pos = getCaretPosition(this.TEXTAREA);
	var endPos = getSelectionEndPosition(this.TEXTAREA);
	var sortByRelevanceSetting = this.cellProperties.sortByRelevance;
	var filterSetting = this.cellProperties.filter;
	var orderByRelevance = null;
	var highlightIndex = null;
	var flipped = null;
	this.choices = choices;

	this.htEditor.loadData(pivot([choices]));
	choicesLength = choices.length;
	this.updateDropdownHeight();
	this.flipDropdownIfNeeded();

	if (this.cellProperties.strict === true)
	{
		this.highlightBestMatchingChoice(highlightIndex);
	}

	this.instance.listen();
	this.TEXTAREA.focus();
	setCaretPosition(this.TEXTAREA, pos, (pos === endPos ? void 0 : endPos));
};

// Function used to convert suggestion array in required array format.
function pivot(arr) {
  var pivotedArr = [];
  if (!arr || arr.length === 0 || !arr[0] || arr[0].length === 0) {
    return pivotedArr;
  }
  var rowCount = arr.length;
  var colCount = arr[0].length;
  for (var i = 0; i < rowCount; i++) {
    for (var j = 0; j < colCount; j++) {
		if (!pivotedArr[j]) {
			pivotedArr[j] = [];
		}

		if (typeof(arr[i][j]) == 'object')
		{
			var checkingstring = arr[i][j];

			for (var key in checkingstring) {
			  if (checkingstring.hasOwnProperty(key)) {
				pivotedArr[j].push([checkingstring[key]]);
			  }
			}
		}
		else
		{
			pivotedArr[j][i] = arr[i][j];
		}
    }
  }
  return pivotedArr;
}

customAutocompleteEditor.prototype.updateDropdownHeight = function() {
  //~ var currentDropdownWidth = this.htEditor.getColWidth(0) + getScrollbarWidth() + 2;
  var currentDropdownWidth = this.htEditor.getColWidth(0) + 2;
  var trimDropdown = this.cellProperties.trimDropdown;

	if (choicesLength > 5)
	{
		this.htEditor.updateSettings({
			height: this.getDropdownHeight(),
			width: trimDropdown ? void 0 : currentDropdownWidth
		});
	}
	this.htEditor.view.wt.wtTable.alignOverlaysWithTrimmingContainer();
};

// Function used to manipulate values once selected from suggestion or typed in cell.
customAutocompleteEditor.prototype.finishEditing = function(restoreOriginalValue) {

	if (!restoreOriginalValue) {
		let thisValue = this.getValue();

		if (thisValue && thisValue.slice(-1) != "|")
		{
			this.setValue(thisValue + "|");
		}

		this.instance.removeHook('beforeKeyDown', Handsontable.onBeforeKeyDown);
	}

	if (this.htEditor && this.htEditor.isListening()) {
		this.instance.listen();
	}

	if (this.htEditor && this.htEditor.getSelected()) {
		var originalVal	= customOrignalValue + "|";
		var value		= this.htEditor.getInstance().getValue();
		var newVal		= originalVal ? originalVal + value + "|" : value + "|";
		
		if (newVal !== void 0) {
			this.setValue(newVal);
		}
	}

  return Handsontable.editors.TextEditor.prototype.finishEditing.apply(this, arguments);
};

/* 
 * Everything below are same as core funtion but are written here to support overrided editor
 * This can be removed from here if the call is forwarded properly.
 * */
function setCaretPosition(element, pos, endPos) {
  if (endPos === void 0) {
    endPos = pos;
  }
  if (element.setSelectionRange) {
    element.focus();
    try {
      element.setSelectionRange(pos, endPos);
    } catch (err) {
      var elementParent = element.parentNode;
      var parentDisplayValue = elementParent.style.display;
      elementParent.style.display = 'block';
      element.setSelectionRange(pos, endPos);
      elementParent.style.display = parentDisplayValue;
    }
  } else if (element.createTextRange) {
    var range = element.createTextRange();
    range.collapse(true);
    range.moveEnd('character', endPos);
    range.moveStart('character', pos);
    range.select();
  }
}

function getCaretPosition(el) {
  if (el.selectionStart) {
    return el.selectionStart;
  } else if (document.selection) {
    el.focus();
    var r = document.selection.createRange();
    if (r == null) {
      return 0;
    }
    var re = el.createTextRange();
    var rc = re.duplicate();
    re.moveToBookmark(r.getBookmark());
    rc.setEndPoint('EndToStart', re);
    return rc.text.length;
  }
  return 0;
}

function getSelectionEndPosition(el) {
  if (el.selectionEnd) {
    return el.selectionEnd;
  } else if (document.selection) {
    var r = document.selection.createRange();
    if (r == null) {
      return 0;
    }
    var re = el.createTextRange();
    return re.text.indexOf(r.text) + r.text.length;
  }
}

var addClass = function (element, className) {
    var len = 0;
    if (typeof className === 'string') {
      className = className.split(' ');
    }
    className = filterEmptyClassNames(className);
    if (isSupportMultipleClassesArg) {
      element.classList.add.apply(element.classList, className);
    } else {
      while (className && className[len]) {
        element.classList.add(className[len]);
        len++;
      }
    }
  };

function filterEmptyClassNames(classNames) {
  var len = 0,
      result = [];
  if (!classNames || !classNames.length) {
    return result;
  }
  while (classNames[len]) {
    result.push(classNames[len]);
    len++;
  }
  return result;
}

var isSupportMultipleClassesArg = (function() {
    var element = document.createElement('div');
    element.classList.add('test', 'test2');
    return element.classList.contains('test2');
  }());

// Register cutomAutoCompleteEditor as customselect.
Handsontable.editors.registerEditor('customselect', customAutocompleteEditor);
