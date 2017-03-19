// Object
function ImageFaceTag(id, name, top, left, width, height) {
	// properties
	this.id = id;
	this.name = name;
	this.top = top;
	this.left = left;
	this.width = width;
	this.height = height;
	this.x1 = left;
	this.y1 = top;
	
	this._div;
	this._textBox;
	this.persisted = false;
	this.divAppended = false;
	this.textBoxAppended = false;
	
	ImageFaceTag.allImageFaceTags.push(this);
}

// static properties
ImageFaceTag.tmpId = -1;
ImageFaceTag.minX2;
ImageFaceTag.maxX2;
ImageFaceTag.minY2;
ImageFaceTag.maxY2;
ImageFaceTag.wsListTagsUrl;
ImageFaceTag.wsChangeTagUrl;
ImageFaceTag.imageId;
ImageFaceTag.imageElement;
ImageFaceTag.allImageFaceTags = [];
ImageFaceTag.active = false;
ImageFaceTag.allFaceTags = [];
ImageFaceTag.allFaceTagNames = [];

// static methods 
ImageFaceTag.createTemp = function(x1, y1) {
	return new ImageFaceTag(ImageFaceTag.tmpId--, "", y1, x1, 0, 0);
}

ImageFaceTag.fetchAndAppend = function(appendToElement) {
	$.ajax({
		method: "POST",
		url: ImageFaceTag.wsListTagsUrl, 
		data: { 
			imageId: ImageFaceTag.imageId
		},
		success: function(answer) {
			var answerObj = JSON.parse(answer);
			var phpImageFaceTags = JSON.parse(answerObj.result);
			
			for(var i=0; i<phpImageFaceTags.length; i++) {
				var imageFaceTag = new ImageFaceTag(phpImageFaceTags[i].tag_id, phpImageFaceTags[i].name, 0, 0, 0, 0);
				imageFaceTag.setRelativePosition(phpImageFaceTags[i].top, phpImageFaceTags[i].left, phpImageFaceTags[i].width, phpImageFaceTags[i].height);
				imageFaceTag.persisted = true
				imageFaceTag.appendTo(appendToElement);
				
				ImageFaceTag.allImageFaceTags.push(imageFaceTag);
			}
		}
	});
}

ImageFaceTag.removeById = function(id) {
	for (var i=0; i<ImageFaceTag.allImageFaceTags.length; i++) {
		if (ImageFaceTag.allImageFaceTags[i].id == id) {
			ImageFaceTag.allImageFaceTags[i].remove();
			return true;
		}
	}
	return false;
}


// public methods
ImageFaceTag.prototype.appendTo = function(element) {
	this.appendDivTo(element);
	this.appendTextBoxTo(element);
}

ImageFaceTag.prototype.appendDivTo = function(element) {
	element.append(this.getDiv());
	this.divAppended = true;
}

ImageFaceTag.prototype.appendTextBoxTo = function(element) {
	element.append(this.getTextBox());
	this.textBoxAppended = true;
}

ImageFaceTag.prototype._createDiv = function() {
	var div = $(document.createElement('div'));
	div.attr('id', "faceTagDiv_" + this.id);
	div.attr("title", this.name);
	div.addClass("facetag-div");
	var that = this;
	div.bind("click", function(event) { that.onClickDiv() } );
	
	div.css({
		position: 'absolute',
		zIndex: 1000,
		top: this.top,
		left: this.left,
		width: this.width,
		height: this.height
	});
	
	return div;
}

ImageFaceTag.prototype._createTextBox = function() {
	var textBox = $(document.createElement('input'));
	textBox.attr('id', "faceTagTextBox_" + this.id);
	textBox.addClass("facetag-textbox");
	var that = this;
	textBox.bind("keydown", function(event) { if(event.keyCode == 13) that.onEnterDownInTextBox(); });
	textBox.bind("blur", function(event) { that.onBlurInTextBox(this); });
	textBox.val(this.name);
	
	textBox.css({
		position: 'absolute',
		zIndex: 1000,
		top: (this.top + this.height),
		left: this.left
	});
	
	textBox.autocomplete({
      source: ImageFaceTag.allFaceTagNames
    });
	
	return textBox;
}

ImageFaceTag.prototype.onClickDiv = function() {
	this.showDiv();
	this.activateTextBox();
}

ImageFaceTag.prototype.onEnterDownInTextBox = function() {
	this.getTextBox().blur();
}

ImageFaceTag.prototype.onBlurInTextBox = function() {
	var faceTagName = this.getTextBox().val().trim();
	
	if (faceTagName == "") {
		this.name = "__DELETE__";
		if (this.persisted) {
			this.dbAjaxChange(0, 0, 0, 0);
			this.persisted = false;
		}
		this.remove();
	} else {
		if (this.name != faceTagName) {
			this.name = faceTagName;
			var relativeTo = ImageFaceTag.imageElement;
			var relativeToPos = relativeTo.offset();
			var divPos = this.getDiv().offset();
			
			var relativeTop = (divPos.top - relativeToPos.top) / relativeTo.height();
			var relativeLeft = (divPos.left - relativeToPos.left) / relativeTo.width();
			var relativeWidth = this.getDiv().width() / relativeTo.width();
			var relativeHeight = this.getDiv().height() / relativeTo.height();
			
			this.dbAjaxChange(relativeTop, relativeLeft, relativeWidth, relativeHeight);
			this.persisted = true;
			
			this.getDiv().attr("title", faceTagName);
		}
		
		if(!ImageFaceTag.active) {
			this.hideDiv();
		}
		this.hideTextBox();
	}	
}

ImageFaceTag.prototype.dbAjaxChange = function(relativeTop, relativeLeft, relativeWidth, relativeHeight) {
	var that = this;
	$.ajax({
		method: "POST",
		url: ImageFaceTag.wsChangeTagUrl, 
		data: { 
			id: that.id,
			imageId: ImageFaceTag.imageId,
			name: that.name,
			top: relativeTop, 
			left: relativeLeft,
			width: relativeWidth,
			height: relativeHeight
		},
		success: function(answer) {
			var result = JSON.parse(JSON.parse(answer)['result']);
			
			if (result['action'] == 'INSERT' || result['action'] == 'UPDATE') {
				ImageFaceTag.removeById(result['id']);
						
				that.id = result['id'];
				that.getDiv().attr('id', 'faceTagDiv' + that.id);
				that.getTextBox().attr('id', 'faceTagTextBox_' + that.id);
				
				ImageFaceTag.allFaceTags[that.id] = that.name;
				var arrayValues = [];
				for (var key in ImageFaceTag.allFaceTags) {
					arrayValues.push(ImageFaceTag.allFaceTags[key]);
				}
				ImageFaceTag.allFaceTagNames = arrayValues;
			}
		}
	});
}

ImageFaceTag.prototype.remove = function() {
	if (this.divAppended) {
		this.getDiv().remove();
		this.divAppended = false;
	}
	if (this.textBoxAppended) {
		this.getTextBox().remove();
		this.textBoxAppended = false;
	}
	
	var newAllImageFaceTags = [];
	for (var i=0; i<ImageFaceTag.allImageFaceTags.length; i++) {
		if (ImageFaceTag.allImageFaceTags[i].id != this.id) {
			newAllImageFaceTags.push(ImageFaceTag.allImageFaceTags[i]);
		}
	}
	ImageFaceTag.allImageFaceTags = newAllImageFaceTags;
}

ImageFaceTag.prototype.setPositionByCoordinates = function(x2, y2) {
	var x2 = Math.max(ImageFaceTag.minX2, Math.min(ImageFaceTag.maxX2, x2));
	var y2 = Math.max(ImageFaceTag.minY2, Math.min(ImageFaceTag.maxY2, y2));
	
	this.top = (this.y1 < y2) ? this.y1 : y2;
	this.left = (this.x1 < x2) ? this.x1 : x2;
	this.width = (this.x1 < x2) ? x2 - this.x1 : this.x1 - x2;
	this.height = (this.y1 < y2) ? y2 - this.y1 : this.y1 - y2;
	
	this.refreshPosition();
}

ImageFaceTag.prototype.setRelativePosition = function(relTop, relLeft, relWidth, relHeight) {
	var element = ImageFaceTag.imageElement;
	var elementPos = element.offset();
	
	this.top = relTop * element.height() + elementPos.top;
	this.left = relLeft * element.width() + elementPos.left;
	this.width = relWidth * element.width();
	this.height = relHeight * element.height();
	
	this.refreshPosition();
}

ImageFaceTag.prototype.refreshPosition = function() {
	this.getDiv().css({
		position: 'absolute',
		zIndex: 1000,
		top: this.top,
		left: this.left,
		width: this.width,
		height: this.height
	});
}

ImageFaceTag.prototype.getDiv = function() {
	if (this._div == undefined) {
		this._div = this._createDiv();
	}
	return this._div;
}

ImageFaceTag.prototype.getTextBox = function() {
	if (this._textBox == undefined) {
		this._textBox = this._createTextBox();
	}
	return this._textBox;
}

ImageFaceTag.prototype.showDiv = function() {
	this.getDiv().addClass("facetag-div_active");
}

ImageFaceTag.prototype.hideDiv = function() {
	this.getDiv().removeClass("facetag-div_active");
}

ImageFaceTag.prototype.activateTextBox = function() {
	var textBox = this.getTextBox();
	textBox.addClass("facetag-textbox_active");
	textBox.focus();
}

ImageFaceTag.prototype.hideTextBox = function() {
	this.getTextBox().removeClass("facetag-textbox_active");
}
