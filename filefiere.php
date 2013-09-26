<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Home:&nbsp;Soluzioni per strutture in legno - rothoblaas</title>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.1/jquery-ui.js"></script>
<!-- <script src="jquerywebcam/jquery.webcam.js"></script> -->
<style>
	body { font-size: 62.5%; }
	label, input, select { display:block; }
	input.text { margin-bottom:12px; width:95%; padding: .4em; }
	select.select { margin-bottom:12px; width:95%; padding: .4em; }
	fieldset { padding:0; border:0; margin-top:25px; }
	h1 { font-size: 1.2em; margin: .6em 0; }
	div#main-container {background-image: url(file_fiere.jpg);  width:1024px; height:768px }
	div#form-container {position:relative; top:150px; left:100px}
	ul#cams {color:#fff;}
	.ui-dialog { padding: .3em; }
	.validateTips { color:#fff; border: 1px solid transparent; padding: 0.3em; }
	p.ui-state-highlight {color:#EC1221;  border: 1px solid red; }
	.ui-state-error {  color:#EC1221; border: 1px solid transparent; padding: 0.3em; padding: .3em; font-style:bold;}
	input.text {width:120px;}
	input.file{margin-bottom:12px; width:350px;padding: .4em;}
	select.select {width:135px;}
	label {color:#fff; font-family:Helvetica, Arial, sans-serif; font-size:12px;}
	a {color:#fff; font-family:Helvetica, Arial, sans-serif; font-size:12px;}
	td {padding:2;}
	#flash {position:absolute;	top:0px;	left:0px;	z-index:5000;	width:100%;	height:500px;	background-color:#c00;	display:none;}
</style>
<script>
$(function() {
	function getUrlVars() {
		var vars = {};
		var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
			vars[key] = value;
		});
		return vars;
	}
	var success_parm = getUrlVars()["success"];
	$( "#submit-button" )
	.button()
	.click(function(e) {
				var bValid = true;
				if ( bValid ) {
					// save locally
				}
				else
				{
					e.preventDefault();
				}
			});
	
	
	$( "#picture_dialog" ).dialog({
		autoOpen: false,
		resizable: false, 
		width: 340,
		height:230,
		modal: true,
		buttons: {
			"OK": function() {
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	if(success_parm && success_parm.toLowerCase() == 'ok')
	{
		$( "#picture_dialog" ).prepend('<h3>Registrazione Avvenuta con Successo</h3><p>I contatti sono disponibili su Client+</p>');
		$( "#picture_dialog" ).dialog("open");
	}
	if(success_parm && success_parm.toLowerCase() != 'ok')
	{
		$( "#picture_dialog" ).prepend('<h3>Errore durante la Registrazione ('+success_parm.toLowerCase()+')</h3>');
		$( "#picture_dialog" ).dialog("open");
	}
	
	
});
</script>
</head>
<body>
	<div id="main-container">
		<div id="form-container">
			<div id="dialog-form">
				<form name="file_form" action="http://crm.rothoblaas.com/modules/Webforms/fileform.php" method="post" accept-charset="utf-8" enctype="multipart/form-data"> 
				<input type="hidden" name="publicid" value="ec11fab0b7d74c34c3a07a83d49039d1"></input>
					<input type="hidden" name="name" value="fileform"></input>
					<input type="hidden" value="File Fiere" name="leadsource"></input>
					<table width="800px">
					<tbody>
						<tr VALIGN=TOP>
							<td>
								<label>File*</label><input name="filename" id="filename" class="file ui-widget-content ui-corner-all" type="file" /><input id="submit-button" type="submit" value="Invia" ></input>
							</td>
							<td colspan=2 rowspan=2>
									<label>Note / Descrizione</label><textarea  type="text" value="" id="description" name="description" rows="12" cols="30" ></textarea >
							</td>
						</tr>
					</tbody>
					</table>
					<p>
					</p>
				</form>
			</div>			
			<div id="picture_dialog" title="Esito Registrazione"></div>
		</div>
	</div>
</body>
</html>
