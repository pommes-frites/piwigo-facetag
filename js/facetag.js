var selecting = false;
var mainImageMap;

var currentImageFaceTag;

function initImageFaceTags(imageId, listTagsUrl, changeTagUrl) {
	ImageFaceTag.imageId = imageId;
	ImageFaceTag.imageElement = $("#theMainImage");
	ImageFaceTag.wsListTagsUrl = listTagsUrl;
	ImageFaceTag.wsChangeTagUrl = changeTagUrl;
	
	fetchAllFaceTags(listTagsUrl);
	
	ImageFaceTag.fetchAndAppend($("body"));

	// disables drag & drop behavior of browsers (e. g. firefox) while the user wants to tag a face
	$(document).on("dragstart", function(e) {
		if (ImageFaceTag.active && e.target.nodeName.toUpperCase() == "IMG" && e.target.id == "theMainImage") {
			return false;
		}
	});
}

function onFaceTagButtonClicked() {	
	if(ImageFaceTag.active) {
		stopFaceTagging();
	} else {
		startFaceTagging();
	}
}

function startFaceTagging() {
	ImageFaceTag.active = true;
	$("#facetag-button_span").addClass("facetag-button_active");
	var mainImage = $("#theMainImage");
	mainImageMap = mainImage.attr("usemap");
	mainImage.removeAttr("usemap");
	mainImage.css("cursor", "crosshair");
	mainImage.bind("mousedown", function(event) { onMouseDownOnPicture(event); });
	
	$(document).bind("mousemove", function(event) { if(selecting) { onMouseMoveWhileSelecting(event); } });
	$(document).bind("mouseup", function(event) { if(selecting) { onMouseUpWhileSelecting(event); } });
	
	var mainImagePos = mainImage.offset();
	ImageFaceTag.minX2 = mainImagePos.left;
	ImageFaceTag.maxX2 = ImageFaceTag.minX2 + mainImage.width();
	ImageFaceTag.minY2 = mainImagePos.top;
	ImageFaceTag.maxY2 = ImageFaceTag.minY2 + mainImage.height();
	
	for(var i=0; i<ImageFaceTag.allImageFaceTags.length; i++) {
		ImageFaceTag.allImageFaceTags[i].showDiv();
	}
}

function stopFaceTagging(activeImageFaceTag) {
	ImageFaceTag.active = false;
	$("#facetag-button_span").removeClass("facetag-button_active");
	var mainImage = $("#theMainImage");
	mainImage.css("cursor", "auto");
	mainImage.unbind("mousedown");
	
	$(document).unbind("mousemove");
	$(document).unbind("mouseup");
	
	mainImage.attr("usemap", mainImageMap);
	
	for(var i=0; i<ImageFaceTag.allImageFaceTags.length; i++) {
		if (activeImageFaceTag == undefined || activeImageFaceTag.id != ImageFaceTag.allImageFaceTags[i].id) {
			ImageFaceTag.allImageFaceTags[i].hideDiv();
		}
	}
}


function onMouseDownOnPicture(e) {
	selecting = true;
	
	currentImageFaceTag = ImageFaceTag.createTemp(e.pageX, e.pageY);
	currentImageFaceTag.showDiv();
	currentImageFaceTag.appendDivTo($("body"));
}

function onMouseMoveWhileSelecting(e) {
	currentImageFaceTag.setPositionByCoordinates(e.pageX, e.pageY);
}

function onMouseUpWhileSelecting(e) {
	selecting = false;
	
	currentImageFaceTag.appendTextBoxTo($("body"));
	currentImageFaceTag.activateTextBox();
	
	stopFaceTagging(currentImageFaceTag);
}

function fetchAllFaceTags(wsUrl) {
	$.ajax({
		method: "POST",
		url: wsUrl, 
		data: { 
			imageId: "-1"
		},
		success: function(answer) {
			var answerObj = JSON.parse(answer);
			var phpFaceTags = JSON.parse(answerObj.result);
			
			ImageFaceTag.allFaceTags = [];
			ImageFaceTag.allFaceTagNames = [];
			for(var i=0; i<phpFaceTags.length; i++) {
				ImageFaceTag.allFaceTags[phpFaceTags[i].id] = phpFaceTags[i].name;
				ImageFaceTag.allFaceTagNames.push(phpFaceTags[i].name);
			}
		}
	});
}
