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
	div#main-container {background-image: url(costrumat_background.jpg);  width:1024px; height:768px }
	div#form-container {position:relative; top:150px; left:100px}
	ul#cams {color:#fff;}
	.ui-dialog { padding: .3em; }
	.validateTips { color:#fff; border: 1px solid transparent; padding: 0.3em; }
	p.ui-state-highlight {color:#EC1221;  border: 1px solid red; }
	.ui-state-error {  color:#EC1221; border: 1px solid transparent; padding: 0.3em; padding: .3em; font-style:bold;}
	input.text {width:120px;}
	select.select {width:135px;}
	label {color:#fff; font-family:Helvetica, Arial, sans-serif; font-size:12px;}
	a {color:#fff; font-family:Helvetica, Arial, sans-serif; font-size:12px;}
	td {padding:2;}
	#flash {position:absolute;	top:0px;	left:0px;	z-index:5000;	width:100%;	height:500px;	background-color:#c00;	display:none;}
</style>
<script>
$(function() {
	
	
	var firstname = $( "#firstname" ),
		email = $( "#email" ),
		lastname = $( "#lastname" ),
		company = $( "#company" ),
		phone = $( "#phone" ),
		privacy = $( "#privacy" ),
		allFields = $( [] ).add( firstname ).add( email ).add( lastname ).add( company ).add(phone).add( privacy ),
		tips = $( ".validateTips" );
	function updateTips( t ) {
		tips
			.text( t )
			.addClass( "ui-state-highlight" );
		setTimeout(function() {
			tips.removeClass( "ui-state-highlight", 1500 );
		}, 500 );
	}
	
	function getUrlVars() {
		var vars = {};
		var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
			vars[key] = value;
		});
		return vars;
	}
	
	function checkLength( o, n, min, max ) {
		if ( o.val().length > max || o.val().length < min ) {
			o.addClass( "ui-state-error" );
			updateTips( "Lunghezza di " + n + " deve essere copresa tra " +
			min + " e " + max + "." );
			return false;
		} else {
			return true;
		}
	}
	function checkRegexp( o, regexp, n ) {
		if ( !( regexp.test( o.val() ) ) ) {
			o.addClass( "ui-state-error" );
			updateTips( n );
			return false;
		} else {
			return true;
		}
	}
	$("body").append("<div id=\"flash\"></div>");
	
	var success_parm = getUrlVars()["success"];
	$( "#submit-button" )
	.button()
	.click(function(e) {
				var bValid = true;
				allFields.removeClass( "ui-state-error" );
				bValid = bValid && checkLength( firstname, "Nome", 3, 16 );
				bValid = bValid && checkLength( lastname, "Cognome", 3, 16 );
				bValid = bValid && checkLength( company, "Azienda", 3, 25 );
				bValid = bValid && checkLength( phone, "Telefono", 3, 25 );
				bValid = bValid && checkLength( email, "email", 6, 80 );
				bValid = bValid && checkRegexp( firstname, /^[a-z]([0-9a-z_ ])+$/i, "Nombre a-z, 0-9 oder _ ." );
				bValid = bValid && checkRegexp( lastname, /^[a-z]([0-9a-z_ ])+$/i, "Apellidos a-z, 0-9 oder _ ." );
				bValid = bValid && checkRegexp( company, /^[a-z]([0-9a-z_ ])+$/i, "Empresa a-z, 0-9 oder _ ." );
				// From jquery.validate.js (by joern), contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
				bValid = bValid && checkRegexp( email, 		/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "eg. nombre.apellidos@empresa.com" );
				if ( bValid ) {
					// save locally
					uploaded_filename = $( "#snapshot").attr("src");
				}
				else
				{
					e.preventDefault();
				}
			});
	/*
	$("#webcam").webcam({
		width: 300,
		height: 225,
		mode: "callback",
		swffile: "jquerywebcam/jscam_canvas_only.swf",
		onSave: saveCB,
		onCapture: function() {
			webcam.save();
		},
		debug: function() {},
		onLoad: function() {
			var cams = webcam.getCameraList();
			for(var i in cams) {
				$("#cams").append("<li>" + cams[i] + "</li>");
			}
		}
	});*/
	
	$( "#dialog-confirm" ).dialog({
		autoOpen: false,
		resizable: false, 
		width: 650,
		height:400,
		modal: true,
		buttons: {
			"Confirmacion": function() {
				$( this ).dialog( "close" );
			}
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
	
	$( "#show-privacy" )
		.click(function(e) {
		$( "#dialog-confirm	" ).dialog( "open" );
		e.preventDefault();
	});
	/*
	$( "#scatta" )
		.click(function(e) {
		webcam.capture(1);
		e.preventDefault();
	});*/
	
	if(success_parm && success_parm.toLowerCase() == 'ok')
	{
		$( "#picture_dialog" ).prepend('<h3>Registrazione avvenuta con successo</h3><p>Disponibile su Client+</p>');
		$( "#picture_dialog" ).dialog("open");
	}
	if(success_parm && success_parm.toLowerCase() != 'ok')
	{
		$( "#picture_dialog" ).prepend('<h3>Registrazione fallita('+success_parm.toLowerCase()+')</h3>');
		$( "#picture_dialog" ).dialog("open");
	}
	
	
});
</script>
</head>
<body>
	<div id="main-container">
		<div id="form-container">
			<div id="dialog-form" title="Registrati!">
				<form name="COSTRUMAT2013" action="http://crm.rothoblaas.com/modules/Webforms/rotho.capture.php" method="post" accept-charset="utf-8">
					<input type="hidden" name="publicid" value="bea8d94d17fd54ac2d31dc8a15722d81"></input>
					<input type="hidden" name="name" value="COSTRUMAT2013"></input>
					<input type="hidden" value="COSTRUMAT 2013" name="leadsource"></input>
					<table width="800px">
					<tbody>
						<tr VALIGN=TOP>
							<td>
								<label for="firstname">Nombre*</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="firstname" id="firstname" required="true"></input>
							</td>
							<td>
								<label>Apellidos*</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="lastname" id="lastname" required="true"></input>
							</td>
							<td>
								<label>Empresa*</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="company" id="company"  required="true"></input>
							</td>
							<td colspan=2 rowspan=4>
									<label>Descripci&oacute;n</label><textarea  type="text" value="" id="description" name="description" rows="12" cols="30" ></textarea >
								<!-- <label for="webcam">Snapshot</label> -->
								<!-- <ul id="cams"></ul> -->
								<!-- <div id="webcam"></div> -->
							</td>
						</tr>
						<tr>
							<td>
								<label>Tel&eacute;fono*</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="phone" id="phone" required="true"></input>
							</td>
							<td>
								<label>Email*</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="email" id="email" required="true"></input>
							</td>
							<td>
								<label>Poblaci&oacute;n</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="city" id="city" ></input>
							</td>
						</tr>
						<tr>
							<td>
								<label>Direcci&oacute;n</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="lane" id="lane" ></input>
							</td>
							<td>
								<label>C&oacute;digo Postal</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="code" id="code" ></input>
							</td>
							<td>
								<label>Pa&iacute;s</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="country"  id="country" ></input>
							</td>
						</tr>
						<tr>
							<td>
								<label>Provincia</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="state"  id="state" ></input>
							</td>
							<td>
								<label>Categor&iacute;a</label>
								<select class="select ui-widget-content ui-corner-all" id="categoria" name="label:Categoria[]">
											<option value="---">
														---
												</option>
												<option value="RC / CARP">
														Carpenteria
												</option>
												<option value="RD / DIST">
														Distribuzione
												</option>
												<option value="RS / SAFE">
														Sicurezza
												</option>
												<option value="RP / PROG">
														Progettazione 
												</option>
												<option value="RE / ALTRO">
														Altro
												</option>
							   </select>
							</td>
							<td>
								<label>Agente</label> <!--cf_1079 => assigned_user_id','assigned_user_id_display','{\"118137-->
								<select class="select ui-widget-content ui-corner-all" id="assigned_user_id" name="label:Assegnato_da_fiera[]">
											<option value="9">
														Rotho Sal&oacute;n
												</option>
											<option value="106">
														GENOVESE ALESSIO
												</option>
											<option value="112">
														ZAMUDIO ALARCON MOISES
												</option>
											<option value="1867">
														LOPEZ SOLANO ANDRES
												</option>
											<option value="111">
														LEAL DE IBARRA ANREIROS MARCOS
												</option>
											<option value="27891">
														HERNANDEZ MANUEL
												</option>
											<option value="109">
														MONAGO JORGE
												</option>
											<option value="105">
														GENOVESE NICOLA
												</option>
											<option value="110">
														JIMENEZ NICOLAS
												</option>
											<option value="113">
														BRAZ RICARDO
												</option>
							   </select>
							</td>
						</tr>
						<tr>
							<td>
								<input id="submit-button" type="submit" value="Registra" ></input>
							</td>
							<td colspan=2 align="right">
								<p class="validateTips">*Obligatorio.</p>
							</td>
							<td colspan=2>
								<a id="show-privacy" href="#" >Privacy</a>
								<input type="checkbox" checked value="privacy"></input>
								<!-- <a id="scatta" href="#">Scatta</a> -->
							</td>
						</tr>
					</tbody>
					</table>
					<p>
					</p>
				</form>
			</div>
			<!-- <button id="create-user">Registrati</button> -->
			
			<div id="picture_dialog" title="Registrazione"></div>
		</div>
		<div id="dialog-confirm"  title="Privacy">
			<div class="csc-default" id="c808">
				<h3><span>Note legali</span></h3>
				<p class="bodytext"><b>Rotho Blaas srl<br></b>Via Dell'Adige N. 2/1<br>I-39040 Cortaccia (BZ)</p>
				<p class="bodytext"><b>Tel:</b> +39 0471 81 84 00<br><b>Fax:</b> +39 0471 81 84 84<br><b>E-mail: </b><a class="mail" href="mailto:info@rothoblaas.com">info@rothoblaas.com</a>
				</p>
				<p class="bodytext"><b>Partita IVA:</b> IT 01433490214
				</p>
				<p class="bodytext"><b>Responsabile per i contenuti: </b>Laura Dalvit
				</p>
				<p class="bodytext"><b>Programmazione, Concepting, Design, CMS:</b> <a class="external-link-new-window" target="_blank" title="Internet Agentur, Web Agency, S&Uuml;dtirol, Alto Adige, CMS, TYPO3, FLASH, Video" href="http://www.lemon.st/">lemon</a></p>
			</div>
			<div style="margin-top:30px;" class="csc-default" id="c809"><h3><span>Privacy</span></h3>
				<p class="bodytext"><b>Informativa sulla privacy (D.LGS. 196/03): Informativa ai sensi del DL 196/2003</b></p>
				<p class="bodytext">
					<b>Finalit&agrave;<br></b>Per poter utilizzare determinati servizi della presente hompage &egrave; richiesta l'immissione di dati personali. L'invio volontario e voluto di dati relativi alla persona del mittente anche tramite posta elettronica agli indirizzi indicati nell'homepage comporta necessariamente la memorizzazione dell'indirizzo e-mail del mittente, procedura necessaria per rispondere alla sua richiesta. Vengono memorizzati altres&igrave; tutti gli altri dati contenuti nell'e-mail. Immettendo i propri dati, il mittente da il suo tacito consenso all'uso degli stessi ai fini pubblicitari e di marketing, fatti salvi i suoi diritti di cui all'art. 7 del decreto legislativo (diritti dell'interessato).<br><b><br>Trattamento dei dati<br></b>In relazione alle suindicate finalit&agrave;, il trattamento dei dati personali avviene mediante strumenti manuali ed automatizzati con modalit&agrave; strettamente correlate alle finalia stesse e comunque, in modo da garantire la sicurezza e la riservatezza dei dati stessi.Categorie dei soggetti ai quali i dati possono essere comunicati<br>Per il perseguimento delle finalit&agrave; i dati possono essere comunicati a terzi; non sono comunque soggetti a diffusione.<br><b><br>I dati dell'interessato possono essere comunicati ai seguenti destinatari:</b>
				</p>
				<ul>
					<li>internamente al responsabile per il trattamento dei dati e a persone da lui incaricate</li>
					<li>societ&agrave; connesse o controllate</li>
					<li>societ&agrave; o aziende deputate al trattamento di dati</li>
				</ul>
				<p class="bodytext">
					I soggetti appartenenti alle categorie ai quali i dati possono essere comunicati utilizzeranno i dati in qualit&agrave; di "titolari" ai sensi della legge, in piena autonomia, essendo estranei all'originario trattamento effettuato presso la Rothoblaas s.r.l.<br><br>Altre pagine web eventualmente raggiungibili tramite link apposti sull'homepage della Rothoblaas potrebbero memorizzare i dati dell'utente. In questo caso il trattamento dei dati, in quanto perpetrato in autonomia da parte del gestore della pagina web &egrave; al di fuori del controllo da parte della Rothoblaas, percui quest'ultima non assume responsabilit&agrave; alcuna.<br><b><br>Diritti dell'interessato<br></b>Informiamo che, come previsto dall'art. 7 del "Codice", l'interessato ha il diritto di conoscere, in ogni momento, l'esistenza o meno di dati personali che lo riguardano e anche,il diritto di ottenere l'aggiornamento, la rettificazione ovvero l'integrazione dei dati e la loro cancellazione. Inoltre l'interessato ha il diritto di opporsi, in tutto o in parte, al trattamento di dati personali che lo riguardano a fini di invio di materiale pubblicitario, per il compimento di ricerche di mercato o di comunicazione commerciale. Infine il diritto di opporsi pu&ograve; essere esercitato per motivi legittimi al trattamento dei dati personali che lo riguardano, ancorch&egrave; pertinenti allo scopo della raccolta.<br>Allo scopo &egrave; sufficiente contattare il responsabile per il trattamento dei dati di cui al capoverso seguente.<br><br><b>Responsabile del trattamento<br></b>Titolare del trattamento dei dati &egrave; la Rothoblaas con sede a Cortaccia, Via dell'Adige 2/1, la quale ha designato come responsabile il signor Reinhard Brunner.<br><br><b>Clausola di manleva<br></b>La presente homepage potrebbe fornire eventuali link ad altri siti o ad altre risorse del web. L'utente riconosce che la Rothoblaas non pu&ograve; in alcun modo controllare ed essere ritenuta responsabile per il funzionamento di tali siti o risorse esterne e per il contenuto degli stessi. Rothoblaas pu&ograve; essere ritenuta responsabile solamente qualora fosse accertato che poteva essere a conoscenza dei contenuti e poteva impedire l'uso di contenuti eventualmente illegali.
				</p>
			</div>
		</div>
	</div>
</body>
</html>
