{html_style}
.nice{
    display: inline-block;
    background: white;
    border-radius: 10px;
    font-family: "arial-black";
    font-size: 14px;
    color: black;
    padding: 4px 8px;
}

.parent {
	width: 100%;
	margin: 0 auto;
	align-content: left;
}

.labels {
	width: 200px;
	float: left;
}

.inputs {
	width: 200px;
	margin-left: 200px;
}

.ta {
	border-radius: 10px 10px 0px 10px;
}

{/html_style}

<!-- Show the title of the plugin -->
<div class="titlePage">
  <h2>{'MugShot'|translate}</h2>
</div>


 <form method="post" id="footer_form" action="{$PLUGIN_ACTION}" class="general">

	<!-- Options -->
	<fieldset class="mainConf">
		<legend>{'Configuration'|translate}</legend>

		<!-- Groups -->
		<div class="parent">
			<div class="labels">
				<label for="group_list">{'Groups:'|translate}</label>
			</div>
			<div class="inputs">
				<input class="nice" size="70" type="text" id="group_list" name="group_list" placeholder="{'Comma,Separated,Group,List'|translate}" value="{$group_list}" />
			</div><br>

			<div class="labels">
				<label for="group_list">
          {'Crop faces for auto tagging*'|@translate}
        </label>
			</div>
			<div class="inputs">
        <label for="autotag" style="width:100%;">
    			{if $autotag }
    				<input type="radio" id="autotag1" name="autotag" value="1" checked />Yes<br>
    				<input type="radio" id="autotag2" name="autotag" value="0"/>No<br>
    			{else}
    				<input type="radio" id="autotag1" name="autotag" value="1"/>Yes<br>
    				<input type="radio" id="autotag2" name="autotag" value="0" checked />No<br>
    			{/if}
        </label>
			</div><br>
      <p style="width:100%">
        *I would like to eventually include automatic facial recognition (server side) based on facial tags provided by users.
        I do not know when I'll actually get to programming this feature. However, by providing the option to crop faces now,
        you can enable this feature and when I eventually implement automatic facial tagging (server side)
        you'll have the data needed for training.
      </p><br>
      <p><i><b>
        It is your responsibility to make sure your users/clients are aware that you are saving snapshots of their face when people tag them.
      </p></i></b><br>
		</div>
	<input type="submit" value="{'Save'|@translate}" name="save" />
</form>
