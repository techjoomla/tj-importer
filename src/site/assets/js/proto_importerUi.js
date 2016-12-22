// prototype patter

var importerUiProto = function(){};

importerUiProto.prototype.view = document.getElementByID("view").value;


importerUiProto.prototype.renderView = function()
{
	alert("in render view");
}

var oneRender = new importerUiProto();
oneRender.renderView
