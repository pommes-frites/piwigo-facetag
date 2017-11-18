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
			</div> <br>
		</div>
	<input type="submit" value="{'Save'|@translate}" name="save" />
</form>
