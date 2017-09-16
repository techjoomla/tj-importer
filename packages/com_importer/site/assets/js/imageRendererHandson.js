
var imageRenderer = function(instance, td, row, col, prop, value, cellProperties){

	while (td.firstChild) {
	  td.removeChild(td.firstChild);
	}

	if(value)
	{

		var splitVal = value.split("|");
		var splitLen = splitVal.length;


		let regexStr		= new RegExp(importerUi.batchImageFields[prop].regex);
		let imageBasePath	= importerUi.batchImageFields[prop].baseurl;

		var mainDiv = document.createElement('DIV');

		for (var i = 0; i < splitLen; i++)
		{
			let imageLink		= splitVal[i];
			let imagePath		= imageLink.replace( regexStr , importerUi.batchImageFields[prop].replacedStr);
			let imageFinalPath	= imageBasePath + imagePath;
			let noImagePath		= imageBasePath + "media/zoo/applications/blog/templates/osian/assets/images/noimage.jpg";

			var imageDiv	= jQuery('<div></div>');
			var imageEle	= jQuery('<img />')
								.attr('src', imageFinalPath)
								.attr('class', 'handson-images')
								.attr('onerror', "this.onerror=null;this.src='" + noImagePath + "';");
			var imagePathDiv	= jQuery('<div></div>').attr('class', 'handson-images-path').text(imageLink);

			imagePathDiv.appendTo(imageDiv);
			imageEle.appendTo(imageDiv);
			imageDiv.appendTo(mainDiv);
		}

		td.appendChild(mainDiv);
	}

	if (importerUi.batchTempInvalid.length && importerUi.batchTempInvalid[row])
	{
		td.style.background = '#CEC';
	}
}

