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
	div#main-container {background-image: url(construct2014.jpg);  width:1024px; height:768px }
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
		country = $( "#country" ),
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
				bValid = bValid && checkLength( firstname, "Nome", 2, 16 );
				bValid = bValid && checkLength( lastname, "Cognome", 2, 16 );
				bValid = bValid && checkLength( company, "Azienda", 2, 25 );
				bValid = bValid && checkLength( phone, "Telefono", 2, 25 );
				bValid = bValid && checkLength( email, "email", 6, 80 );
				// From jquery.validate.js (by joern), contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
				bValid = bValid && checkRegexp( email, 		/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "eg. nome.cognome@azienda.com" );
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
			"Confermo e approvo": function() {
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
		$( "#picture_dialog" ).prepend('<h3>Account successfully inserted!</h3><p></p>');
		$( "#picture_dialog" ).dialog("open");
	}
	if(success_parm && success_parm.toLowerCase() != 'ok')
	{
		$( "#picture_dialog" ).prepend('<h3>Error during insertion ('+success_parm.toLowerCase()+')</h3>');
		$( "#picture_dialog" ).dialog("open");
	}
	
	
});
</script>
</head>
<!-- danzi.tn@20140319 -->
<body>
	<div id="main-container">
		<div id="form-container">
			<div id="dialog-form" title="Registrati!">
				<form name="EASY" action="http://crm.rothoblaas.com/modules/Webforms/rotho.capture.php" method="post" accept-charset="utf-8">
					<input type="hidden" name="publicid" value="824a079a2c9ba8c2ef73181d66e3bca8"></input>
					<input type="hidden" name="name" value="EASY"></input>
					<input type="hidden" id="rdrct"  name="rdrct" value="construct2014"></input>
					<input type="hidden" value="14 CONSTRUCT EXPO_RO" name="leadsource"></input>
					<table width="800px">
					<tbody>
						<tr VALIGN=TOP>
							<td>
								<label for="firstname">First Name*</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="firstname" id="firstname" required="true"></input>
							</td>
							<td>
								<label>Last Name*</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="lastname" id="lastname" required="true"></input>
							</td>
							<td>
								<label>Company*</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="company" id="company"  required="true"></input>
							</td>
							<td colspan=2 rowspan=4>
									<label>Note</label><textarea  type="text" value="" id="description" name="description" rows="14" cols="45" ></textarea >
								<!-- <label for="webcam">Snapshot</label> -->
								<!-- <ul id="cams"></ul> -->
								<!-- <div id="webcam"></div> -->
							</td>
						</tr>
						<tr>
							<td>
								<label>Phone*</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="phone" id="phone" required="true"></input>
							</td>
							<td>
								<label>Email*</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="email" id="email" required="true"></input>
							</td>
							<td>
								<label>City</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="city" id="city" ></input>
							</td>
						</tr>
						<tr>
							<td>
								<label>Street</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="lane" id="lane" ></input>
							</td>
							<td>
								<label>ZIP Code</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="code" id="code" ></input>
							</td>
							<td>
								<label>Country*</label>
								<!-- <input type="text"  class="text ui-widget-content ui-corner-all" value="" name="country"  id="country" ></input> -->
								<select class="select ui-widget-content ui-corner-all" id="country" name="country">
									<option value="RO">Romania</option>
									<option value="MD">Moldova, Republic of</option>
									<option value="BG">Bulgaria</option>
									<option value="RS">Serbia</option>
									<option value="UA">Ukraine</option>
									<option value="CZ">Czech Republic</option>
									<option value="DE">Germania</option>
									<option value="AT">Austria</option>
									<option value="SE">Sweden</option>
									<option value="DK">Denmark</option>
									<option value="NO">Norway</option>
									<option value="PL">Poland</option>
									<option value="LT">Lithuania</option>
									<option value="EE">Estonia</option>
									<option value="LV">Latvia</option>
									<option value="RU">Russian Federation</option>
									<option value="FR">Francia</option>
									<option value="AF">Afghanistan</option>
									<option value="AX">Aland Islands</option>
									<option value="AL">Albania</option>
									<option value="DZ">Algeria</option>
									<option value="AS">American Samoa</option>
									<option value="AD">Andorra</option>
									<option value="AO">Angola</option>
									<option value="AI">Anguilla</option>
									<option value="AQ">Antarctica</option>
									<option value="AG">Antigua and Barbuda</option>
									<option value="AR">Argentina</option>
									<option value="AM">Armenia</option>
									<option value="AW">Aruba</option>
									<option value="AU">Australia</option>
									<option value="AZ">Azerbaijan</option>
									<option value="BS">Bahamas</option>
									<option value="BH">Bahrain</option>
									<option value="BD">Bangladesh</option>
									<option value="BB">Barbados</option>
									<option value="BY">Belarus</option>
									<option value="BE">Belgium</option>
									<option value="BZ">Belize</option>
									<option value="BJ">Benin</option>
									<option value="BM">Bermuda</option>
									<option value="BT">Bhutan</option>
									<option value="BO">Bolivia</option>
									<option value="BA">Bosnia and Herzegovina</option>
									<option value="BW">Botswana</option>
									<option value="BV">Bouvet Island</option>
									<option value="BR">Brazil</option>
									<option value="IO">British Indian Ocean Territory</option>
									<option value="BN">Brunei Darussalam</option>
									<option value="BF">Burkina Faso</option>
									<option value="BI">Burundi</option>
									<option value="KH">Cambodia</option>
									<option value="CM">Cameroon</option>
									<option value="CA">Canada</option>
									<option value="CV">Cape Verde</option>
									<option value="KY">Cayman Islands</option>
									<option value="CF">Central African Republic</option>
									<option value="TD">Chad</option>
									<option value="CL">Chile</option>
									<option value="CN">China</option>
									<option value="CX">Christmas Island</option>
									<option value="CC">Cocos (Keeling) Islands</option>
									<option value="CO">Colombia</option>
									<option value="KM">Comoros</option>
									<option value="CG">Congo</option>
									<option value="CD">Congo, The Democratic Republic of The</option>
									<option value="CK">Cook Islands</option>
									<option value="CR">Costa Rica</option>
									<option value="CI">Cote D'ivoire</option>
									<option value="HR">Croatia</option>
									<option value="CU">Cuba</option>
									<option value="CY">Cyprus</option>
									<option value="DJ">Djibouti</option>
									<option value="DM">Dominica</option>
									<option value="DO">Dominican Republic</option>
									<option value="EC">Ecuador</option>
									<option value="EG">Egypt</option>
									<option value="SV">El Salvador</option>
									<option value="GQ">Equatorial Guinea</option>
									<option value="ER">Eritrea</option>
									<option value="ET">Ethiopia</option>
									<option value="FK">Falkland Islands (Malvinas)</option>
									<option value="FO">Faroe Islands</option>
									<option value="FJ">Fiji</option>
									<option value="FI">Finland</option>
									<option value="GF">French Guiana</option>
									<option value="PF">French Polynesia</option>
									<option value="TF">French Southern Territories</option>
									<option value="GA">Gabon</option>
									<option value="GM">Gambia</option>
									<option value="GE">Georgia</option>
									<option value="DE">Deutschland</option>
									<option value="GH">Ghana</option>
									<option value="GI">Gibraltar</option>
									<option value="GR">Greece</option>
									<option value="GL">Greenland</option>
									<option value="GD">Grenada</option>
									<option value="GP">Guadeloupe</option>
									<option value="GU">Guam</option>
									<option value="GT">Guatemala</option>
									<option value="GG">Guernsey</option>
									<option value="GN">Guinea</option>
									<option value="GW">Guinea-bissau</option>
									<option value="GY">Guyana</option>
									<option value="HT">Haiti</option>
									<option value="HM">Heard Island and Mcdonald Islands</option>
									<option value="VA">Holy See (Vatican City State)</option>
									<option value="HN">Honduras</option>
									<option value="HK">Hong Kong</option>
									<option value="HU">Hungary</option>
									<option value="IS">Iceland</option>
									<option value="IN">India</option>
									<option value="ID">Indonesia</option>
									<option value="IR">Iran, Islamic Republic of</option>
									<option value="IQ">Iraq</option>
									<option value="IE">Ireland</option>
									<option value="IM">Isle of Man</option>
									<option value="IL">Israel</option>
									<option value="IT">Italy</option>
									<option value="JM">Jamaica</option>
									<option value="JP">Japan</option>
									<option value="JE">Jersey</option>
									<option value="JO">Jordan</option>
									<option value="KZ">Kazakhstan</option>
									<option value="KE">Kenya</option>
									<option value="KI">Kiribati</option>
									<option value="KP">Korea, Democratic People's Republic of</option>
									<option value="KR">Korea, Republic of</option>
									<option value="KW">Kuwait</option>
									<option value="KG">Kyrgyzstan</option>
									<option value="LA">Lao People's Democratic Republic</option>
									<option value="LB">Lebanon</option>
									<option value="LS">Lesotho</option>
									<option value="LR">Liberia</option>
									<option value="LY">Libyan Arab Jamahiriya</option>
									<option value="LI">Liechtenstein</option>
									<option value="LU">Luxembourg</option>
									<option value="MO">Macao</option>
									<option value="MK">Macedonia, The Former Yugoslav Republic of</option>
									<option value="MG">Madagascar</option>
									<option value="MW">Malawi</option>
									<option value="MY">Malaysia</option>
									<option value="MV">Maldives</option>
									<option value="ML">Mali</option>
									<option value="MT">Malta</option>
									<option value="MH">Marshall Islands</option>
									<option value="MQ">Martinique</option>
									<option value="MR">Mauritania</option>
									<option value="MU">Mauritius</option>
									<option value="YT">Mayotte</option>
									<option value="MX">Mexico</option>
									<option value="FM">Micronesia, Federated States of</option>
									<option value="MC">Monaco</option>
									<option value="MN">Mongolia</option>
									<option value="ME">Montenegro</option>
									<option value="MS">Montserrat</option>
									<option value="MA">Morocco</option>
									<option value="MZ">Mozambique</option>
									<option value="MM">Myanmar</option>
									<option value="NA">Namibia</option>
									<option value="NR">Nauru</option>
									<option value="NP">Nepal</option>
									<option value="NL">Netherlands</option>
									<option value="AN">Netherlands Antilles</option>
									<option value="NC">New Caledonia</option>
									<option value="NZ">New Zealand</option>
									<option value="NI">Nicaragua</option>
									<option value="NE">Niger</option>
									<option value="NG">Nigeria</option>
									<option value="NU">Niue</option>
									<option value="NF">Norfolk Island</option>
									<option value="MP">Northern Mariana Islands</option>
									<option value="NO">Norway</option>
									<option value="OM">Oman</option>
									<option value="PK">Pakistan</option>
									<option value="PW">Palau</option>
									<option value="PS">Palestinian Territory, Occupied</option>
									<option value="PA">Panama</option>
									<option value="PG">Papua New Guinea</option>
									<option value="PY">Paraguay</option>
									<option value="PE">Peru</option>
									<option value="PH">Philippines</option>
									<option value="PN">Pitcairn</option>
									<option value="PT">Portugal</option>
									<option value="PR">Puerto Rico</option>
									<option value="QA">Qatar</option>
									<option value="RE">Reunion</option>
									<option value="RW">Rwanda</option>
									<option value="SH">Saint Helena</option>
									<option value="KN">Saint Kitts and Nevis</option>
									<option value="LC">Saint Lucia</option>
									<option value="PM">Saint Pierre and Miquelon</option>
									<option value="VC">Saint Vincent and The Grenadines</option>
									<option value="WS">Samoa</option>
									<option value="SM">San Marino</option>
									<option value="ST">Sao Tome and Principe</option>
									<option value="SA">Saudi Arabia</option>
									<option value="SN">Senegal</option>
									<option value="SC">Seychelles</option>
									<option value="SL">Sierra Leone</option>
									<option value="SG">Singapore</option>
									<option value="SK">Slovakia</option>
									<option value="SI">Slovenia</option>
									<option value="SB">Solomon Islands</option>
									<option value="SO">Somalia</option>
									<option value="ZA">South Africa</option>
									<option value="GS">South Georgia and The South Sandwich Islands</option>
									<option value="ES">Spain</option>
									<option value="LK">Sri Lanka</option>
									<option value="SD">Sudan</option>
									<option value="SR">Suriname</option>
									<option value="SJ">Svalbard and Jan Mayen</option>
									<option value="SZ">Swaziland</option>
									<option value="CH">Switzerland</option>
									<option value="SY">Syrian Arab Republic</option>
									<option value="TW">Taiwan, Province of China</option>
									<option value="TJ">Tajikistan</option>
									<option value="TZ">Tanzania, United Republic of</option>
									<option value="TH">Thailand</option>
									<option value="TL">Timor-leste</option>
									<option value="TG">Togo</option>
									<option value="TK">Tokelau</option>
									<option value="TO">Tonga</option>
									<option value="TT">Trinidad and Tobago</option>
									<option value="TN">Tunisia</option>
									<option value="TR">Turkey</option>
									<option value="TM">Turkmenistan</option>
									<option value="TC">Turks and Caicos Islands</option>
									<option value="TV">Tuvalu</option>
									<option value="UG">Uganda</option>
									<option value="AE">United Arab Emirates</option>
									<option value="GB">United Kingdom</option>
									<option value="US">United States</option>
									<option value="UM">United States Minor Outlying Islands</option>
									<option value="UY">Uruguay</option>
									<option value="UZ">Uzbekistan</option>
									<option value="VU">Vanuatu</option>
									<option value="VE">Venezuela</option>
									<option value="VN">Viet Nam</option>
									<option value="VG">Virgin Islands, British</option>
									<option value="VI">Virgin Islands, U.S.</option>
									<option value="WF">Wallis and Futuna</option>
									<option value="EH">Western Sahara</option>
									<option value="YE">Yemen</option>
									<option value="ZM">Zambia</option>
									<option value="ZW">Zimbabwe</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label>State</label><input type="text"  class="text ui-widget-content ui-corner-all" value="" name="state"  id="state" ></input>
							</td>
							<td>
								<label>Category</label>
								<select class="select ui-widget-content ui-corner-all" id="categoria" name="label:Categoria[]">
											<option value="---">
														---
												</option>
												<option value="RC / CARP">
														RC / CARP
												</option>
												<option value="RD / DIST">
														RD / DIST
												</option>
												<option value="RS / SAFE">
														RS / SAFE
												</option>
												<option value="RP / PROG">
														RP / PROG
												</option>
												<option value="RE / ALTRO">
														RE / ALTRO
												</option>
							   </select>
							</td>
							<td>
								<label>Agente</label> 	<!--assigned_user_id','assigned_user_id_display','{\"118137-->
								<select class="select ui-widget-content ui-corner-all" id="assigned_user_id" name="assigned_user_id">
												<option value="9">Fair User (Generic)</option>
												<option value="137">IONUT PREDA</option>
												<option value="136">LUCIAN LUPU</option>
												<option value="132856">ALEXANDRU RADU</option>
												<option value="138">KASCO LEVENTE </option>	
												
							   </select>
							</td>
						</tr>
						<tr>
							<td>
								<input id="submit-button" type="submit" value="Registrati" ></input>
							</td>
							<td>
								<p class="validateTips">*Required fields</p>
							</td>
							<td align="right">
								<input type="checkbox" checked value="privacy"></input>
							</td>
							<td colspan=2>
								<a id="show-privacy" href="#" >Privacy</a>
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
			
			<div id="picture_dialog" title="Registration process"></div>
		</div>
		<div id="dialog-confirm"  title="Note Legali e Privacy">
			<div class="csc-default" id="c808">
				<h3><span>Note legali</span></h3>
				<p class="bodytext"><b>Rotho Blaas srl<br></b>Via Dell'Adige N. 2/1<br>I-39040 Cortaccia (BZ)</p>
				<p class="bodytext"><b>Tel:</b> +39 0471 81 84 00<br><b>Fax:</b> +39 0471 81 84 84<br><b>E-mail: </b><a class="mail" href="mailto:info@rothoblaas.com">info@rothoblaas.com</a>
				</p>
				<p class="bodytext"><b>Partita IVA:</b> IT 01433490214
				</p>
				<p class="bodytext"><b>Responsabile per i contenuti: </b>Laura Dalvit
				</p>
				<p class="bodytext"><b>Programmazione, Concepting, Design, CMS:</b> <a class="external-link-new-window" target="_blank" title="Internet Agentur, Web Agency, Südtirol, Alto Adige, CMS, TYPO3, FLASH, Video" href="http://www.lemon.st/">lemon</a></p>
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
