{strip}
{combine_css id="facetag.style" path=$FACETAG_PATH|cat:"css/style-bootstrap_darkroom.css"}
{combine_css id="jquery.ui" path="themes/default/js/ui/theme/jquery.ui.all.css"}
{combine_script id="jquery" load="async"}
{combine_script id="jquery.ui" load="async"}
{combine_script id="jquery.ui.menu" load="async"}
{combine_script id="jquery.ui.autocomplete" load="async"}
{combine_script id="facetag.object.scripts" load="async" path=$FACETAG_PATH|cat:"js/facetagObject.js"}
{combine_script id="facetag.scripts" load="async" path=$FACETAG_PATH|cat:"js/facetag.js"}

{* <!-- nothing more than the button itself must be defined here --> *}

{footer_script require='jquery,facetag.object.scripts,facetag.scripts'}{literal}
$(document).ready(function() {
	{/literal}{$FACETAG_ONLOAD}{literal};
});
{/literal}{/footer_script}

<li>
  <a href="javascript:void(0)" onclick="{$BUTTON_ONCLICK}" title="{'Tag a face'|@translate}" class="facetag-button-link pwg-state-default pwg-button" rel="nofollow">
    <span id="facetag-button_span" class="glyphicon glyphicon-tags facetag-button"> </span>
    <span class="glyphicon-text">{'FaceTag'|@translate}</span>
  </a>
</li>
{/strip}