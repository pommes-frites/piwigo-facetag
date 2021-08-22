{html_style}{literal}
.nice{
    display: inline-block;
    background: white;
    border-radius: 5px;
    font-family: "arial-black";
    font-size: 14px;
    color: black;
    padding: 4px 8px;
}
.parent {width: 100%;margin: 0 auto;align-content: left;}
.labels {width: 200px;float: left;}
.inputs {width: 200px;margin-left: 200px;}
.ta {border-radius:5px 5px 5px 5px;}
input[type="radio"] {
  margin-right: 10px;
}
.tagbox {
  width:581px;
  height:1.25em;
  padding:5px 8px 2px;
  border:1px solid #787777a6;
  margin-right:25px;
  background: white;
}
{/literal}{/html_style}

{combine_script id='LocalStorageCache' load='footer' path='admin/themes/default/js/LocalStorageCache.js'}
{combine_script id='jquery.selectize' load='footer' path='themes/default/js/plugins/selectize.min.js'}
{combine_css id='jquery.selectize' path="themes/default/js/plugins/selectize.{$themeconf.colorscheme}.css"}

{html_style}{literal}
form p {text-align:left;}
{/literal}{/html_style}

{footer_script}
jQuery(document).ready(function() {
  var groupsCache = new GroupsCache({
    serverKey: '{$CACHE_KEYS.groups}',
    serverId: '{$CACHE_KEYS._hash}',
    rootUrl: '{$ROOT_URL}'
  });
  groupsCache.selectize(jQuery('[data-selectize=groups]'));
});
{/footer_script}

<!-- Show the title of the plugin -->
<div class="titlePage">
  <h2>{'MugShot'|translate}</h2>
</div>

 <form method="post" id="footer_form" action="{$PLUGIN_ACTION}" class="general">

		<!-- Groups --><br>
		<div class="parent">
      <p>
    {if count($groups) > 0}
        <strong>{'The following groups can use MugShot to tag people'|@translate}</strong>
        <br><br>
        <select data-selectize="groups" data-value="{$groups_selected|@json_encode|escape:html}"
          placeholder="{'Type in a search term'|translate}"
          name="groups[]" multiple style="width:600px;"></select>
    {else}
        {'You have not created any groups!'|@translate} <a href="admin.php?page=group_list" class="externalLink">{'Group management'|@translate}</a>
    {/if}
      </p>

      <p><br>
      <label for="max_tags">
      <strong>{'Maximum number of tags to load client side for auto complete functionality'|@translate}</strong><br><br>
      <input id="max_tags" type="text" size="2" maxlength="3" name="max_tags" value="{$max_tags|default:'500'}" class="tagbox nice">
      </p>

    <!-- Enable automatic cropping of tagged faces -->
			<p><br>
        <label for="autotag">
        <strong>{'Enable cropping of faces for automated tagging'|@translate}</strong><br>
        <i>{'This requires ImageMagick to be installed, including the PHP plugin'|@translate}</i><br><br>
    			{if $autotag }
    				<input type="radio" id="autotag1" name="autotag" value="1" checked /><b><i>{'ALLOW'|@translate}</i></b> {'MugShot to crop tagged faces from photos'|@translate}<br>
    				<input type="radio" id="autotag2" name="autotag" value="0"/><b><i>D{'DO NOT ALLOW'|@translate}</i></b> {'MugShot to crop tagged faces from photos'|@translate}<br>
    			{else}
    				<input type="radio" id="autotag1" name="autotag" value="1"/><b><i>{'ALLOW'|@translate}</i></b> {'MugShot to crop tagged faces from photos'|@translate}<br>
    				<input type="radio" id="autotag2" name="autotag" value="0" checked /><b><i>{'DO NOT ALLOW'|@translate}</i></b> {'MugShot to crop tagged faces from photos'|@translate}<br>
    			{/if}
        </label>
			</p><br>
		</div>

    <!-- Enable the face-api button
			<p><br>
        <label for="faceapi">
        <strong>{'Enable button for identifying facial landmarks with face-api'|@translate}</strong><br>
        <i>This is primarily for testing</i><br><br>
    			{if $autotag }
    				<input type="radio" id="autotag1" name="autotag" value="1" checked /><b><i>ALLOW</i></b> MugShot to crop tagged faces from photos<br>
    				<input type="radio" id="autotag2" name="autotag" value="0"/><b><i>DO NOT ALLOW</i></b> MugShot to crop tagged faces from photos<br>
    			{else}
    				<input type="radio" id="autotag1" name="autotag" value="1"/><b><i>ALLOW</i></b> MugShot to crop tagged faces from photos<br>
    				<input type="radio" id="autotag2" name="autotag" value="0" checked /><b><i>DO NOT ALLOW</i></b> MugShot to crop tagged faces from photos<br>
    			{/if}
        </label>
			</p><br>
		</div> -->

    {if count($groups) > 0}
    	<p class="formButtons">
    		<input type="submit" value="{'Save'|@translate}" name="save" />
    	</p>
    {/if}
</form>
