var $ajaxCall;
var importerFormUi = {

	formOverlay : "",
	formContainerDiv : "",
	currentRow	: "",
	handlebarTemplate : "",
	totalRows : 0,

	construct : function(){
		importerFormUi.formOverlay 	= jQuery("#myNav");

		importerFormUi.formOverlay.css('height', "100%");

		var overlay = jQuery('<div id="overlay-child" hidden> </div>');
		importerFormUi.formOverlay.append(overlay);

		return;
	},

	constructHeader : function(){

		importerFormUi.totalRows = importerFormServices.getTotalRows();
		importerFormUi.currentRow = importerFormServices.getActiveRow();

		var headerCoverDiv = jQuery("<div></div>").attr('class', 'overlay-header-cover fixed');
		var headerDiv = jQuery("<div></div>").attr('class', 'overlay-header container');

		var saveButton	= importerUi.createButton('JForm["save-form"]', 'save-form btn btn-success', 'save-form', 'Save');
		var saveCloseButton	= importerUi.createButton('JForm["save-close-form"]', 'save-close-form btn btn-success', 'save-close-form', 'Save and close');
		var prevButton	= importerUi.createButton('JForm["prev"]', 'prev btn btn-info', 'prev', '<<');
		var nxtButton	= importerUi.createButton('JForm["next"]', 'next btn btn-info', 'next', '>>');

		nxtButton.on('click', { method : importerFormUi.loadNextRow, message : 'Please Wait..' }, importerFormUi.showChildOverlay);
		prevButton.on('click',  { method : importerFormUi.loadPrevRow, message : 'Please Wait..' }, importerFormUi.showChildOverlay);
		saveButton.on('click', { method : importerFormUi.saveFormData, message : 'Saving data' }, importerFormUi.showChildOverlay);
		saveCloseButton.on('click', { method : importerFormUi.saveFormData, saveCallback : importerFormUi.destroy, message : 'Saving data' }, importerFormUi.showChildOverlay);

		headerDiv.append(saveButton);
		headerDiv.append(saveCloseButton);
		headerDiv.append(prevButton);

		var infoSpan = jQuery("<span></span>")
						.attr('class', 'recordInfoDiv')
						.text( importerFormUi.currentRow + " of " + importerFormUi.totalRows);

		headerDiv.append(infoSpan);
		headerDiv.append(nxtButton);

		var closeIcon	= jQuery("<a></a>").attr("href", "javascript:void(0)").attr("class", "closebtn").text("x");

		closeIcon.on('click', importerFormUi.destroy);
		headerDiv.append(closeIcon);

		headerCoverDiv.append(headerDiv);

		importerFormUi.formOverlay.append(headerCoverDiv);
	},

	constructForm : function(){

		var formFields = importerFormServices.getColumns();

		var formCoverDiv = jQuery("<div></div>")
							.attr('class', 'entry overlay-content container bulktool-form')
							.attr('id', 'bulktool-form-original')
							.css('border', 'solid 2px green');

		var hiddenFormCoverDiv = jQuery("<div></div>")
									.attr('class', 'entry overlay-content container bulktool-form formHidden')
									.attr('id', 'bulktool-form-hidden')
									.css('border', 'solid 2px red')
									.css('display', 'none');

		var formElement	 = jQuery("<form></form>").attr('class', 'form-horizontal');

		for(var fieldDetails of formFields)
		{
			var createdField = importerFormUi.createFields(fieldDetails);
			createdField.appendTo(formElement);
		}

		importerFormUi.formContainerDiv = formElement[0].outerHTML;
		importerFormUi.handlebarTemplate = Handlebars.compile(importerFormUi.formContainerDiv);
		importerFormUi.currentRow = importerFormServices.getActiveRow();
		var context = importerFormServices.getActiveRowData(importerFormUi.currentRow);
		var html    = importerFormUi.handlebarTemplate(context);

		formCoverDiv.append(html);
		formCoverDiv.change(importerFormUi.valueChanged);
		hiddenFormCoverDiv.append(html);
		importerFormUi.formOverlay.append(formCoverDiv);
		importerFormUi.formOverlay.append(hiddenFormCoverDiv);

		importerFormUi.refreshHeader();
		importerFormUi.showInvalidData();

	},

	valueChanged : function (){
		var isUpdated = importerFormServices.isDataUpdated(importerFormUi.currentRow);

		if(isUpdated)
		{
			jQuery("#bulktool-form-original").css('border', 'solid 2px red');
		}
		else
		{
			jQuery("#bulktool-form-original").css('border', 'solid 2px green');
		}

		return;
	},

	createFields : function(fieldDetails){
			var readOnlyFlag = fieldDetails.readOnly;
			var fieldCover = jQuery("<div></div>").attr('class', 'control-group ');
			var fieldLabel = jQuery("<label></label>").attr('class', 'control-label ').attr('for', fieldDetails.id).text(fieldDetails.name).appendTo(fieldCover);

			var returnFieldObj;

			switch(fieldDetails.type){
				case 'autocomplete' :
						returnFieldObj = jQuery("<input></input>")
											.attr('type', 'text')
											.attr('id', fieldDetails.id)
											.attr('name', fieldDetails.id)
											.attr('class', "form-control ")
											.attr('value', '{{' + fieldDetails.id + '}}')
											.attr('disabled', readOnlyFlag);
						
						if(fieldDetails.option.length)
						{
							returnFieldObj.addClass('autocomplete-options customAutoComplete')
										.attr('data-options', JSON.stringify(fieldDetails.option))
										.attr('placeholder', "Select Value");
						}
						else
						{
							returnFieldObj.addClass('autocomplete-dynamic customAutoComplete')
											.attr('placeholder', 'Search & Select Value');
						}

						returnFieldObj.on("focus", importerFormUi.showDropDown);
					break;
				case 'textarea' :
						returnFieldObj = jQuery('<textarea></textarea>')
											.attr('class', "form-control")
											.attr('id', fieldDetails.id)
											.attr('name', fieldDetails.id)
											.text('{{'+fieldDetails.id+'}}')
											.css('min-height', '300px');

					break;
				default :
						returnFieldObj = jQuery("<input></input>")
											.attr('type', 'text')
											.attr('id', fieldDetails.id)
											.attr('name', fieldDetails.id)
											.attr('class', "form-control")
											.attr('value', '{{' + fieldDetails.id + '}}')
											.attr('disabled', readOnlyFlag);
					break;
			}

			returnFieldObj.appendTo(fieldCover);

			return fieldCover;

		},

	checkKeys : function(){
			var forId = jQuery(this).attr('id');
			if(event.keyCode === 9)
			{
				importerFormUi.hideDropDown(forId);
			}

			return;
		},

	fetchNewSuggestions : function(){

			var forId = jQuery(this).attr('id');
			var fieldVal = jQuery(this).val();
		
			var queryVal	=  fieldVal.split("|");
			var queryValue	= queryVal[queryVal.length - 1];
		
			if($ajaxCall && $ajaxCall.readyState != 4){
				$ajaxCall.abort();
			}

			jQuery(".custom-dropdown[dropdown-for='" + forId + "'] ul li").remove();
			jQuery(".custom-dropdown[dropdown-for='" + forId + "'] ul").append('<li class="loding-dropdown-msg">Loading</li>');

			$ajaxCall = importerFormServices.getSuggestions(queryValue, forId);

			$ajaxCall.fail(
				function() {
					importerUi.showErrorBox();
				}
			).done(
				function() {
					var suggestions	= jQuery.parseJSON($ajaxCall.responseText);
					let noSerchText = "No suggestion for ";

					if(!suggestions.length)
					{
						jQuery(".custom-dropdown[dropdown-for='" + forId + "'] ul li").remove();
						jQuery(".custom-dropdown[dropdown-for='" + forId + "'] ul").append('<li class="norecords-dropdown-msg">No records found</li>');
					}
					else
					{
						var suggestionsArr = [];

						suggestions.forEach(function(element)
						{
							var selectVal = element[element.length-1];

							var dropdownLi = jQuery('<li></li>')
												.attr('data-for', forId)
												.attr('data-value', selectVal + '|');

							var eleArray = [];

							element.forEach(function(ele){
								jQuery('<div></div>').text(ele).appendTo(dropdownLi);
							});

							suggestionsArr.push(dropdownLi);
						});

						jQuery(".custom-dropdown[dropdown-for='" + forId + "'] ul li").remove();
						jQuery(".custom-dropdown[dropdown-for='" + forId + "'] ul").append(suggestionsArr);
						jQuery(".custom-dropdown[dropdown-for='" + forId + "'] ul li").on('click', importerFormUi.setThisMultipleOption);
						importerFormUi.highlightSuggestion(queryValue, forId);
					}

				}
			);
	},

	showDropDown : function()
	{
		var predefinedOptions = jQuery(this).attr('data-options');
		var fieldId = jQuery(this).attr('id');

		jQuery('.custom-dropdown[dropdown-for="' + fieldId + '"]').remove();

		if ( predefinedOptions && predefinedOptions !== undefined )
		{
			// get array of options
			var optionsArray = JSON.parse(predefinedOptions);

			var domDropdown = importerFormUi.createDropdown(fieldId, optionsArray);
			jQuery('li', domDropdown).on('click', importerFormUi.setThisOption);
			jQuery('#' + fieldId).after(domDropdown);
		}
		else
		{
			var domDropdown = importerFormUi.createDropdown(fieldId, []);
			jQuery('#' + fieldId).after(domDropdown);
		}

		jQuery('body').on('click',function(event){
			event.stopPropagation();
			if(jQuery(event.target).attr('id') == fieldId || jQuery(event.target).closest('.custom-dropdown').length)
			{
			}
			else
			{
				importerFormUi.hideDropDown(fieldId);
			}
		});
	},

	hideDropDown : function(targetId){
		//~ var targetId = jQuery(this).attr('id');
		setTimeout(function(){
			jQuery('.custom-dropdown[dropdown-for="' + targetId + '"]').hide();
		}, 100);
	},

	setThisOption : function(){
		event.preventDefault();

		var optionValue = jQuery(this).attr('data-value');
		var inputIt		= '#' + jQuery(this).attr('data-for');

		jQuery(inputIt).val(optionValue);
		jQuery('#bulktool-form-original').trigger("change");
		importerFormUi.hideDropDown(jQuery(this).attr('data-for'));
	},

	setThisMultipleOption : function(){
		event.preventDefault();

		var optionValue = jQuery(this).attr('data-value');
		var inputIt		= '#' + jQuery(this).attr('data-for');

		var queryVal	=  jQuery(inputIt).val().split("|");
		queryVal.pop();
		var customOrignalValue = queryVal.join("|");
		customOrignalValue = customOrignalValue + "|" + optionValue;

		jQuery(inputIt).val(customOrignalValue);
		jQuery('#bulktool-form-original').trigger("change");
		importerFormUi.hideDropDown(jQuery(this).attr('data-for'));
	},

	createDropdown : function (forId, optionsArray)
	{
		var dropdownCover = jQuery('<div></div>')
							.attr('class', 'custom-dropdown')
							.attr('dropdown-for', forId);
		var dropdownUl	  = jQuery('<ul></ul>');

		if(optionsArray.length)
		{
			optionsArray.forEach(function(element){
				var dropdownLi = jQuery('<li></li>')
									.attr('data-for', forId)
									.attr('data-value', element)
									.text(element);

				dropdownLi.appendTo(dropdownUl);
			});
		}
		else
		{
			var fieldVal = jQuery('#' + forId).val();
		
			var queryVal	=  fieldVal.split("|");
			var queryValue	= queryVal[queryVal.length - 1];

			dropdownUl.append('<li class="loding-dropdown-msg">Loading</li>');

			if($ajaxCall && $ajaxCall.readyState != 4){
				$ajaxCall.abort();
			}

			$ajaxCall = importerFormServices.getSuggestions(queryValue, forId);

			$ajaxCall.fail(
				function() {
					importerUi.showErrorBox();
				}
			).done(
				function() {
					var suggestions	= jQuery.parseJSON($ajaxCall.responseText);
					let noSerchText = "No suggestion for ";

					if(!suggestions.length)
					{
						suggestions = [[noSerchText]];
					}
					else
					{
						var suggestionsArr = [];

						suggestions.forEach(function(element){

							var selectVal = element[element.length-1];

							var dropdownLi = jQuery('<li></li>')
												.attr('data-for', forId)
												.attr('data-value', selectVal + '|');

							var eleArray = [];

							element.forEach(function(ele){
								jQuery('<div></div>').text(ele).appendTo(dropdownLi);
							});

							suggestionsArr.push(dropdownLi);
						});

						jQuery(".custom-dropdown[dropdown-for='" + forId + "'] ul li").remove();
						jQuery(".custom-dropdown[dropdown-for='" + forId + "'] ul").append(suggestionsArr);
						jQuery(".custom-dropdown[dropdown-for='" + forId + "'] ul li").on('click', importerFormUi.setThisMultipleOption);
						importerFormUi.highlightSuggestion(queryValue, forId);
					}
				}
			);
		}

		dropdownUl.appendTo(dropdownCover);

		return dropdownCover;
	},

	highlightSuggestion : function (string, forId) {
		var thisEle = jQuery(".custom-dropdown[dropdown-for='" + forId + "'] ul li");

		thisEle.each(function () {
			jQuery("div", this).each(function () {			
				var matchStart = jQuery(this).text().toLowerCase().indexOf("" + string.toLowerCase() + "");

				if(matchStart !== -1)
				{	
					var matchEnd = matchStart + string.length - 1;
					var beforeMatch = jQuery(this).text().slice(0, matchStart);
					var matchText = jQuery(this).text().slice(matchStart, matchEnd + 1);
					var afterMatch = jQuery(this).text().slice(matchEnd + 1);
					jQuery(this).html(beforeMatch + "<em>" + matchText + "</em>" + afterMatch);
				}
			});
		});
    },

	showChildOverlay : function(callback){

		var saveCallback = '';

		if (callback.data.hasOwnProperty('saveCallback'))
		{
			saveCallback = callback.data.saveCallback;
		}

		jQuery("#overlay-child").html("<span>" + callback.data.message + "</span>").css('color', 'red').show("fast", function(){callback.data.method(saveCallback)});
		return true;
	},

	hideChildOverlay : function(message){
		jQuery("#overlay-child").html("<span>" + message + "</span>").css('color', 'green');

		setTimeout(function() {
			jQuery("#overlay-child").hide();
		}, 1000)

		return true;
	},

	saveFormData : function(callback = ''){

		var serForObj = importerFormServices.getFormDataObject();

		importerFormServices.setDataAtRow(serForObj, importerFormUi.currentRow);

		importerFormUi.hideChildOverlay("Data saved to handsontable");

		jQuery("#bulktool-form-hidden form").remove();
		jQuery("#bulktool-form-hidden").append(jQuery("#bulktool-form-original form").clone());

		importerFormUi.valueChanged();
		importerFormUi.refreshHeader();

		if(callback)
		{
			callback();
		}

		return true;
	},

	loadNextRow : function(){

		var isUpdated = importerFormServices.isDataUpdated(importerFormUi.currentRow);

		if (isUpdated)
		{
			var r = confirm("Data has been changed, would you like to update it handsontable");
			if (r == true) {
				var callback = {data : {}};
				callback.data['message']		= "Saving form data..";
				callback.data['method']			= importerFormUi.saveFormData;
				callback.data['saveCallback']	= importerFormUi.loadNextRow;
				importerFormUi.showChildOverlay(callback);
			}
			else
			{
				importerFormUi.switchRow(++importerFormUi.currentRow);
			}
		}
		else
		{
			importerFormUi.switchRow(++importerFormUi.currentRow);
		}
	},

	loadPrevRow : function(){

		var isUpdated = importerFormServices.isDataUpdated(importerFormUi.currentRow);

		if (isUpdated)
		{
			var r = confirm("Data has been changed, would you like to update it handsontable");
			if (r == true) {
				var callback = {data : {}};
				callback.data['message']		= "Saving form data..";
				callback.data['method']			= importerFormUi.saveFormData;
				callback.data['saveCallback']	= importerFormUi.loadPrevRow;
				importerFormUi.showChildOverlay(callback);
			}
			else
			{
				importerFormUi.switchRow(--importerFormUi.currentRow);
			}
		}
		else
		{
			importerFormUi.switchRow(--importerFormUi.currentRow);
		}
	},

	switchRow : function(switchRownNumber)
	{

		var context = importerFormServices.getActiveRowData(switchRownNumber);

		var html    = importerFormUi.handlebarTemplate(context);

		jQuery(".entry.overlay-content.container.bulktool-form form").remove();
		jQuery(".entry.overlay-content.container.bulktool-form").append(html);

		importerFormUi.hideChildOverlay("Please wait...");
		importerFormUi.refreshHeader();
		importerFormUi.showInvalidData();
	},

	showInvalidData : function()
	{
		var invalidFields = importerFormServices.getInvalidFields(importerFormUi.currentRow);

		if(invalidFields && invalidFields.length)
		{
			jQuery('#bulktool-form-original .control-label ').each(function(i, obj) {
				var thisId = jQuery(obj).attr('for');

				if( invalidFields.indexOf(thisId) > -1)
				{
					jQuery( obj )
					  .closest( ".control-group " )
					  .addClass( "invalid" );
				}
			});
		}
	},

	refreshHeader : function(){
		var totalRows = importerFormServices.getTotalRows();

		if(importerFormUi.currentRow === 1 && importerFormUi.currentRow === totalRows)
		{
			jQuery('.prev.btn').attr('disabled', true);
			jQuery('.next.btn').attr('disabled', true);
		}
		else if(importerFormUi.currentRow === 1)
		{
			jQuery('.prev.btn').attr('disabled', true);
			jQuery('.next.btn').attr('disabled', false);
		}
		else if(importerFormUi.currentRow === totalRows)
		{
			jQuery('.next.btn').attr('disabled', true);
			jQuery('.prev.btn').attr('disabled', false);
		}
		else
		{
			jQuery('.next.btn').attr('disabled', false);
			jQuery('.prev.btn').attr('disabled', false);
		}

		jQuery('.recordInfoDiv').text(importerFormUi.currentRow + ' of ' + totalRows);

		jQuery('.customAutoComplete').on('focusin', importerFormUi.showDropDown);
		//~ jQuery('.customAutoComplete').on('focusout', importerFormUi.hideDropDown);
		jQuery('.autocomplete-dynamic.customAutoComplete').on('keyup', importerFormUi.fetchNewSuggestions);
		jQuery('.autocomplete-dynamic.customAutoComplete').on('keydown', importerFormUi.checkKeys);
		
		importerFormUi.valueChanged();
	},

	destroy : function (){
		setTimeout(function(){
			importerFormUi.formOverlay.html("");
			importerFormUi.formOverlay.css('height', '0');
			importerFormServices.setActiveRow(importerFormUi.currentRow);
		}, 500);
	},

	initFormView : function(){
		if(importerFormServices.getActiveRow())
		{
			importerFormUi.construct();
			importerFormUi.constructHeader();
			importerFormUi.constructForm();
		}
		else
		{
			alert("Select a row first");
		}
	}
}

jQuery(document).ready(function(){
	Handlebars.registerHelper('ifCond', function (v1, operator, v2, options) {

		switch (operator) {
			case '==':
				return (v1 == v2) ? options.fn(this) : options.inverse(this);
			case '===':
				return (v1 === v2) ? options.fn(this) : options.inverse(this);
			case '!=':
				return (v1 != v2) ? options.fn(this) : options.inverse(this);
			case '!==':
				return (v1 !== v2) ? options.fn(this) : options.inverse(this);
			case '<':
				return (v1 < v2) ? options.fn(this) : options.inverse(this);
			case '<=':
				return (v1 <= v2) ? options.fn(this) : options.inverse(this);
			case '>':
				return (v1 > v2) ? options.fn(this) : options.inverse(this);
			case '>=':
				return (v1 >= v2) ? options.fn(this) : options.inverse(this);
			case '&&':
				return (v1 && v2) ? options.fn(this) : options.inverse(this);
			case '||':
				return (v1 || v2) ? options.fn(this) : options.inverse(this);
			default:
				return options.inverse(this);
		}
	});
	
	//~ jQuery('input.customAutoComplete').focusin(function(){
		//~ console.log("checking on focus");
	//~ })
});
