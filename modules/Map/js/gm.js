var local_markersArray = [];
var heatMapData = [];
var local_circleArray = [];
var markerCluster = null;
var infoPolyWindow;
var clust_markers = [];
var polyHullSet = {};
var map = null;
var heatmap = null;
var convexHullSet = {};
var gmap = null;
var fusion_value = 0;
var amountrange_value = 0;
var minval = 0;
var maxval = 1;
var maxInt = 0;
var directionsDisplay = null;
var coords = new Object();
coords.lat = 46.329938;
coords.lng = 11.052490000000034;
var geocoder = null;
geocoder = new google.maps.Geocoder();

// danzi.tn@20140902 modifica api
// danzi.tn@20141212 nova classificazione cf_762 sostituito con vtiger_account.account_client_type
// danzi.tn@20150213 aggiornamento slider MAP conforme all'elenco Aziende
// danzi.tn@20150331 modifica allo slider (updateMap), per step da 500 euro
// danzi.tn@20150414 modifica alla infowindow e a circle
// danzi.tn@20150618 aggiunto dati utente e heatMapData
function initialize() {
	directionsDisplay = new google.maps.DirectionsRenderer();
	
	
	// Create and Center a Map
    	var myOptions = {
      		zoom: 6,
      		center: home_center,
            panControl: true,
            zoomControl: true,
            mapTypeControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.TOP_RIGHT
                },
            streetViewControl: false
    	};


	map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);

		
	
	directionsDisplay.setMap(map);
	directionsDisplay.setPanel(document.getElementById("route"));
    
	checkMap('ND');
	
	var domElement = document.getElementById('type1');
	google.maps.event.addDomListener(domElement, 'click',  function() { checkMap(this.id);} );
	domElement = document.getElementById('type2');
	google.maps.event.addDomListener(domElement, 'click',  function() { checkMap(this.id);} );
	domElement = document.getElementById('type3');
	google.maps.event.addDomListener(domElement, 'click',  function() { checkMap(this.id);} );
	domElement = document.getElementById('clust1');
	google.maps.event.addDomListener(domElement, 'click',  function() { checkMap(this.id);} );
	domElement = document.getElementById('clust2');
	google.maps.event.addDomListener(domElement, 'click',  function() { checkMap(this.id);} );
	//updateValueFilterContainer
	domElement = document.getElementById('valueSel');
	domElementND = document.getElementById('valueSelND');
	domElementPROD = document.getElementById('valueSelPROD');
	google.maps.event.addDomListener(domElement, 'click',  function() { updateValueFilterContainer(this);} );
	google.maps.event.addDomListener(domElementND, 'click',  function() { updateValueFilterContainer(this);} );
	google.maps.event.addDomListener(domElementPROD, 'click',  function() { updateValueFilterContainer(this);} );
    
    
   
}

function initializeGmap() 
{     
	var latlng = new google.maps.LatLng(coords.lat, coords.lng);
	var myOptions = {
	  zoom: 10,
	  center: latlng
	};
	gmap = new google.maps.Map(document.getElementById("gmap_canvas"),  myOptions);                         
}  
function showmap() {
	var latlng = new google.maps.LatLng(coords.lat, coords.lng);
	gmap.setCenter(latlng, 10);
    
	var marker = new google.maps.Marker({
		map: gmap,
		position: latlng
	    });
}


function clearClusters(e) {
	e.preventDefault();
	e.stopPropagation();
	markerCluster.clearMarkers();
}

   
function getMarker2(pos, name, type, desc, icon, map_value) { // Andrea Danzi aggiunto type - 24.03.2012
	var infowindow = new google.maps.InfoWindow({
    		content: desc
	});

	var image = new google.maps.MarkerImage( // Andrea Danzi aggiunto custom marker - 24.03.2012
	  icon,
	  new google.maps.Size(32,37),
	  new google.maps.Point(0,0),
	  new google.maps.Point(16,37)
	);

	var shadow = new google.maps.MarkerImage( // Andrea Danzi aggiunto custom marker - 24.03.2012
	  'modules/Map/img/shadow.png',
	  new google.maps.Size(54,37),
	  new google.maps.Point(0,0),
	  new google.maps.Point(16,37)
	);

	var shape = { // Andrea Danzi aggiunto custom marker - 24.03.2012
	  coord: [29,0,30,1,31,2,31,3,31,4,31,5,31,6,31,7,31,8,31,9,31,10,31,11,31,12,31,13,31,14,31,15,31,16,31,17,31,18,31,19,31,20,31,21,31,22,31,23,31,24,31,25,31,26,31,27,31,28,31,29,30,30,29,31,23,32,22,33,21,34,20,35,19,36,12,36,11,35,10,34,9,33,8,32,2,31,1,30,0,29,0,28,0,27,0,26,0,25,0,24,0,23,0,22,0,21,0,20,0,19,0,18,0,17,0,16,0,15,0,14,0,13,0,12,0,11,0,10,0,9,0,8,0,7,0,6,0,5,0,4,0,3,0,2,1,1,2,0,29,0],
	  type: 'poly'
	};

	
	var marker = null;
	if(clusterRequest == 'Enable')
	{
		marker = new google.maps.Marker({
				position: pos,
			icon: image, // Andrea Danzi aggiunto custom marker - 24.03.2012
			shadow: shadow, // Andrea Danzi aggiunto custom marker - 24.03.2012
			shape: shape, // Andrea Danzi aggiunto custom marker - 24.03.2012
				title: name + "|" + type + "|" + map_value
		});
	} else {
		marker = new google.maps.Marker({
				position: pos,
			map: map,
			icon: image, // Andrea Danzi aggiunto custom marker - 24.03.2012
			shadow: shadow, // Andrea Danzi aggiunto custom marker - 24.03.2012
			shape: shape, // Andrea Danzi aggiunto custom marker - 24.03.2012
				title: name + "|" + type + "|" + map_value
		});
	}
	marker.set("map_value",map_value);
	google.maps.event.addListener(marker, 'click', function() {
  		infowindow.open(map,marker);
	});

	return marker;
}

function getDescription(id, pos, name, type, map_value, city, extra, map_aurea,resJson)
{
	var html = "";
	switch(module)
	{
		case "Accounts": 
			html += "<br/><b><span><a href='index.php?module="+module+"&action=DetailView&record="+id+"'>"+name+"</a></b>";
			break;
		case "Leads": 
			html += "<br/><b><span><a href='index.php?module="+module+"&action=DetailView&record="+id+"'>"+name+"</a></b>";
			break;
		case "SalesOrder": 
			html += "<br/><b><span><a href='index.php?action=CallRelatedList&module=Accounts&selected_header=Sales Order&relation_id=4&record="+id+"'>"+name+"</a></b>";
			break;
		case "HelpDesk":
			html += "<br/><b><span><a href='index.php?action=CallRelatedList&module=Contacts&selected_header=HelpDesk&relation_id=21&record="+id+"'>"+name+"</a></b>";
			break;
		case "Potentials": 
			html += "<br/><b><span><a href='index.php?action=CallRelatedList&module=Accounts&selected_header=Potentials&relation_id=2&record="+id+"'>"+name+"</a></b>";
			break;
	}
	if(type)
	{
		html += "<br/>"+type;
	}
	if(map_value)
	{
		html += "<br/>"+numberToCurrency(map_value); // if(module!="HelpDesk") 
	}
	if(map_aurea)
	{
	//	html += "<br/>" + map_aurea;
	}
	
	if(extra)
	{
		html = html + "<br/><br/><div class='checkaddress'><a onClick='check_address(\""+id+"\",\""+resJson["street"]+"\",\""+resJson["code"]+"\",\""+resJson["city"]+"\",\""+resJson["state"]+"\",\""+resJson["country"]+"\");' href='javascript:void(0)'>"+extra+"</a></div>";
	}

	html += "<br/><a onClick='loadDirectionFrom(\""+pos.lat()+","+pos.lng()+"\",\""+name+"\",\""+city+"\")' href='javascript:void(0)'>"+from_lbl+ " " + name + "</a>";
	html += "<br/><a onClick='loadDirectionTo(\""+pos.lat()+","+pos.lng()+"\",\""+name+"\",\""+city+"\")' href='javascript:void(0)'>"+to_lbl+ " " +name+"</a>";
	//html += "<span ><a href='index.php?module="+module+"&action=DetailView&record="+id+"'>View</a></span>";

	// index.php?module=Accounts&action=DetailView&record=154
	return html;
}

function check_address(ekey,street,code,city,state,country) {
	document.getElementById('entity_id').value = ekey;
	document.getElementById('indirizzo').value = street;
	document.getElementById('cap').value = code;
	document.getElementById('citta').value = city;
	document.getElementById('provincia').value = state;
	document.getElementById('stato').value = country;
	$( "#dialog-form" ).dialog( "open" );
	return False;
}

function getMarkerFromResults(sType,sPot) { // Andrea Danzi aggiunto custom marker - 24.03.2012
	if (sType) {
		var sIcon = "modules/Map/img/symbol_pi.png";
		var sCheckpot = "OK";
		if(module=="Leads")
		{
			var firstChar = sType.substring(0,1).toLowerCase();
			sIcon = "modules/Map/img/letter_"+ firstChar +".png";
		}
		else
		{
			switch(sType)
			{
				case "Reseller": // "RC / CARP" "RD / DIST" "RS / SAFE" "RP / PROG" "RE / ALTRO" "RC / DIST" "RC / PROG" "---" "-" 
				  sIcon = "modules/Map/img/letter_r.png";
				  if( sPot == sCheckpot ) sIcon = "modules/Map/img/letter_r-p.png";
				  break;
				case "Competitor":
				  sIcon = "modules/Map/img/letter_c.png";
				  if( sPot == sCheckpot ) sIcon = "modules/Map/img/letter_c-p.png";
				  break;
				case "Partner":
				  sIcon = "modules/Map/img/letter_p.png";
				  if( sPot == sCheckpot ) sIcon = "modules/Map/img/letter_p-p.png";
				  break;
				case "Other":
				  sIcon = "modules/Map/img/letter_o.png";
				  if( sPot == sCheckpot ) sIcon = "modules/Map/img/letter_o-p.png";
				  break;
				case "UTILIZZATORE":
				  sIcon = "modules/Map/img/rc-carp.png";
				  if( sPot == sCheckpot ) sIcon = "modules/Map/img/rc-carp-p.png";
				  break;
				case "RIVENDITORE":
				  sIcon = "modules/Map/img/rd-dist.png";
				  if( sPot == sCheckpot ) sIcon = "modules/Map/img/rd-dist-p.png";
				  break;
				case "PROGETTISTA":
				  sIcon = "modules/Map/img/rp-prog.png";
				  if( sPot == sCheckpot ) sIcon = "modules/Map/img/rp-prog-p.png";
				  break;
				case "INFLUENZATORE":
				  sIcon = "modules/Map/img/letter_i.png";
				  if( sPot == sCheckpot ) sIcon = "modules/Map/img/letter_i-p.png";
				  break;
				case "---":
				  sIcon = "modules/Map/img/symbol_minus.png";
				  if( sPot == sCheckpot ) sIcon = "modules/Map/img/symbol_minus-p.png";
				  break;
				case "-":
				  sIcon = "modules/Map/img/symbol_minus.png";
				  if( sPot == sCheckpot ) sIcon = "modules/Map/img/symbol_minus-p.png";
				  break;
				default:
				  sIcon = "modules/Map/img/letter_d.png";
				  if( sPot == sCheckpot ) sIcon = "modules/Map/img/letter_d-p.png";
			}
		}
		return sIcon;
	}
	else {
		sIcon = "modules/Map/img/letter_d.png";
        if( sPot == sCheckpot ) sIcon = "modules/Map/img/letter_d-p.png";
		return sIcon;
	}
}

function loadDirection(location,name,city)
{
	to = location;
	var request = {
    		origin:from, 
    		destination:to,
    		travelMode: google.maps.DirectionsTravelMode.DRIVING
  	};
  	directionsService.route(request, function(response, status) {
    		if (status == google.maps.DirectionsStatus.OK) {
      			directionsDisplay.setDirections(response);
  		}
	});	
	var ddesc = document.getElementById("desc");
	ddesc.innerHTML = from_lbl+": <span style='font-weight: bold'>"+baseName+" - "+baseCity+"</span> <span style='color:grey; font-size: smaller'>)</span><br/>"+to_lbl+": &nbsp;<span style='font-weight: bold'>"+name+" - "+city+"</span>";
}

function loadDirectionFrom(location,name,city)
{
	gmfrom = location;
	document.getElementById('gmfrom').value = gmfrom;
	document.getElementById('accfrom').value = name + " - " + city;
	gmto = document.getElementById('gmto').value;
	accto = document.getElementById('accto').value;
	if( gmto!='yyy' && gmfrom !='xxx')
	{
		var request = {
	    		origin:gmfrom, 
	    		destination:gmto,
	    		travelMode: google.maps.DirectionsTravelMode.DRIVING
	  	};
	  	directionsService.route(request, function(response, status) {
	    		if (status == google.maps.DirectionsStatus.OK) {
	      			directionsDisplay.setDirections(response);
	  		}
		});	
		var ddesc = document.getElementById("desc");
		ddesc.innerHTML = from_lbl+": <span style='font-weight: bold'>"+name+" - "+city+"</span>><br/>"+to_lbl+": &nbsp;<span style='font-weight: bold'>" + accto + "</span>";
	}
}


function loadDirectionTo(location,name,city)
{
	gmto = location;
	document.getElementById('gmto').value = gmto;
	document.getElementById('accto').value = name + " - " + city;
	gmfrom = document.getElementById('gmfrom').value;
	accfrom = document.getElementById('accfrom').value;
	if( gmto!='yyy' && gmfrom !='xxx')
	{
		var request = {
	    		origin:gmfrom, 
	    		destination:gmto,
	    		travelMode: google.maps.DirectionsTravelMode.DRIVING
	  	};
	  	directionsService.route(request, function(response, status) {
	    		if (status == google.maps.DirectionsStatus.OK) {
	      			directionsDisplay.setDirections(response);
	  		}
		});	
		var ddesc = document.getElementById("desc");
		ddesc.innerHTML = from_lbl+": <span style='font-weight: bold'>"+accfrom+"</span><br/>"+to_lbl+": &nbsp;<span style='font-weight: bold'>"+name+" - "+city+"</span>";
	}
}

function restore()
{
	var ddesc = document.getElementById("desc");
	ddesc.innerHTML = "";
	directionsDisplay.setMap(null);
	var r = document.getElementById("route");
	r.innerHTML = "";
}

function clearCircleArray() {
  if (local_circleArray) {
    for ( i in local_circleArray) {
	  if(local_circleArray[i] && typeof local_circleArray[i] == "object" && typeof local_circleArray[i].setMap == "function"){
	    local_circleArray[i].setVisible(false);
	  }
    }
  }
}


function toggleHeatmap() {
  clearMapCluster();
  deleteCircleArray();
  deleteMarkersArray();
  if(heatmap == null) {
    showHeatMapData();
  } else {
    if(heatmap.getMap()) {
       checkMap();
    } else {
       heatmap.setMap(map);
       for(var key in polyHullSet) {
            var polyHull = polyHullSet[key];
            polyHull.setMap(map);
       }
    }
    // heatmap.setMap(heatmap.getMap() ? null : map);
  }
}

// heatMapData
function clearheatMapData() {
  if (heatMapData) {
    for ( i in heatMapData) {
	  if(heatMapData[i] && typeof heatMapData[i] == "object" && typeof heatMapData[i].setMap == "function"){
	    heatMapData[i].setVisible(false);
	  }
    }
  }
}

function clearMarkersArray() {
  if (local_markersArray) {
    for (i in local_markersArray) {
	  if(local_markersArray[i] && typeof local_markersArray[i] == "object" && typeof local_markersArray[i].setMap == "function"){
	    local_markersArray[i].setVisible(false);
	  }
    }
  }	
}

function showCircleArray() {
  if (local_circleArray) {
    for ( i in local_circleArray) {
	  if(local_circleArray[i] && typeof local_circleArray[i] == "object" && typeof local_circleArray[i].setMap == "function"){
		var map_value = local_circleArray[i].get("map_value");
		// if(fusion_value > map_value || type_or_valueRequest == 'type')
		if(  minval > map_value || maxval < map_value  || type_or_valueRequest == 'type')
		{
			local_circleArray[i].setVisible(false);
		}
		else
		{
			local_circleArray[i].setVisible(true);
		}
	  }
    }
  }
}

function CustomConvexPolygon(options) {
     var self = this;
     // initialize any options
     console.log('init')
}

CustomConvexPolygon.prototype = new google.maps.Polygon();
CustomConvexPolygon.prototype.convex = null;

if (!google.maps.Polygon.prototype.setPolyName) {
	google.maps.Polygon.prototype.setPolyName = function(name) {
		this.polyname = name;
	};
}

if (!google.maps.Polygon.prototype.getPolyName) {
	google.maps.Polygon.prototype.getPolyName = function() {
		return this.polyname;
	};
}

// heatMapData
function showHeatMapData() {
     for( convexHullKey in convexHullSet ) {
         convexHull = convexHullSet[convexHullKey];
         if (convexHull.points.length > 0) {
            var polyHull = polyHullSet[convexHullKey];
            if(polyHull == null) {
                var hullPoints = convexHull.getHull();
                //Convert to google latlng objects
                hullPointsLatLng = hullPoints.map(function (item) {
                    return new google.maps.LatLng(item.y, item.x);
                });
                polyHull = new google.maps.Polygon({
                    paths: hullPointsLatLng,
                    strokeColor: '#000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#000',
                    fillOpacity: 0.35
                });
                polyHull.set("hullpoints",hullPoints);
                polyHull.setPolyName(convexHullKey);
                polyHullSet[convexHullKey] = polyHull;
                google.maps.event.addListener(polyHull, 'click', function (event) {
                        var vertices = this.getPath();
                        var hp = this.get("hullpoints");
                        var polyName = this.getPolyName();
                        var contentString = '<b>Agent Map</b><br>' +
                          'Clicked '+polyName+' on location: <br>' + event.latLng.lat() + ',' + event.latLng.lng() +
                          '<br>';

                        // Iterate over the vertices.
                        for (var i =0; i < vertices.getLength(); i++) {
                            var xy = vertices.getAt(i);
                            // contentString += '<br>' + 'Coordinate ' + i + ':<br>' + xy.lat() + ',' +    xy.lng();
                        }
                        var userName = "";
                        // Iterate over the vertices.
                        for (var i =0; i < hp.length; i++) {
                            var hpoint = hp[i];
                            userName = hpoint.info.user_name;
                            // contentString += '<br>' + 'Coordinate ' + i + ':<br>' + xy.lat() + ',' +    xy.lng();
                        }
                      contentString += userName;
                      // Replace the info window's content and position.
                      infoPolyWindow.setContent(contentString);
                      infoPolyWindow.setPosition(event.latLng);

                      infoPolyWindow.open(map);
                    });    
            }
            polyHull.setMap(map);    
            infoPolyWindow = new google.maps.InfoWindow();
        }
    }
    if (heatMapData) {
        heatmap = new google.maps.visualization.HeatmapLayer({
          data: heatMapData,
          radius: 20,
          maxIntensity: maxInt,
        });
        heatmap.setMap(map);
    }
  
   
  
}

function showMarkersArray() {
  if (local_markersArray) {
    for (i in local_markersArray) {
	  if(local_markersArray[i] && typeof local_markersArray[i] == "object" && typeof local_markersArray[i].setMap == "function"){
		var map_value = local_markersArray[i].get("map_value");
		// if(fusion_value > map_value || type_or_valueRequest == 'value')
		if(  minval > map_value || maxval < map_value  || type_or_valueRequest == 'value')
		{
			local_markersArray[i].setVisible(false);
		}
		else
		{
			local_markersArray[i].setVisible(true);
		}
	  }
    }
  }	
}

function deleteCircleArray() {
  if (local_circleArray && local_circleArray.length > 0) {
    for (i in local_circleArray) {
	  if(local_circleArray[i] && typeof local_circleArray[i] == "object" && typeof local_circleArray[i].setMap == "function"){
	    local_circleArray[i].setMap(null);
	  }
    }
    local_circleArray.length = 0;
    local_circleArray = [];
  }	
}

function deleteMarkersArray() {
  if (local_markersArray && local_markersArray.length > 0) {
    for (i in local_markersArray) {
	  if(local_markersArray[i] && typeof local_markersArray[i] == "object" && typeof local_markersArray[i].setMap == "function"){
	    local_markersArray[i].setMap(null);
	  }
    }
    local_markersArray.length = 0;
	local_markersArray = [];
  }	
}


function checkMap(name) {
    if(heatmap) heatmap.setMap(null);
    for(var key in polyHullSet) {
        var polyHull = polyHullSet[key];
        polyHull.setMap(null);
    }
	clearMapCluster();
	deleteCircleArray();
	deleteMarkersArray();
	if(name=='clust2') clusterRequest = 'Disable';
	if(name=='clust1') clusterRequest = 'Enable';
	if(name=='type1') type_or_valueRequest = 'type';
	if(name=='type2') type_or_valueRequest = 'value_and_type';
	if(name=='type3') type_or_valueRequest = 'value';
	createArrays();
	if(clusterRequest=='Enable')
	{
		showMapCluster();
	} 
}

function createArrays() {
    
    maxInt = 0;
    
	for (var j in resultLayer) 
	{
		var result = resultLayer[j];
		// if(fusion_value > result["map_value"]) continue;
		if( minval > result["map_value"] || result["map_value"] > maxval ) continue;
		var pos = new google.maps.LatLng(result["pos"][0], result["pos"][1]);
        sType = result["last_name"] + " " + result["first_name"] + " ("+result["user_name"]+")<br/>"; //  danzi.tn@20150618 aggiunto dati utente
        
        if(result["type_trans"])
        {
            sType += "<br/>"+result["type_trans"];
        }
        if(result["account_line"] && result["account_line"] != "---")
        {
            sType += " - " +result["account_line"];
        }
        if(result["account_main_activity"] && result["account_main_activity"] != "---")
        {
            sType += "<br/>"+result["account_main_activity"];
        }
        if(result["account_sec_activity"] && result["account_sec_activity"] != "---")
        {
            sType += "<br/>"+result["account_sec_activity"];
        }
		var contentString = getDescription(j, pos ,result["name"] ,sType ,result["map_value"],result["city"],result["extra"],result["map_aurea"],result);
		// pos, name, type, desc, icon
		if( result["city"] == "Chur")
		{
			myLat = result["pos"][0];
			myLon = result["pos"][1];
		}
		if( type_or_valueRequest != 'value' )
		{
			//var markerItem = getMarker2(pos,result["name"],result["type"],contentString,getMarkerFromResults(result["type"],result["map_aurea"]),result["map_value"]);
			//local_markersArray.push(markerItem);
			var m_icon = getMarkerFromResults(result["type"],result["map_aurea"]);
			var m_image = new google.maps.MarkerImage( // Andrea Danzi aggiunto custom marker - 24.03.2012
			  m_icon,
			  new google.maps.Size(32,37),
			  new google.maps.Point(0,0),
			  new google.maps.Point(16,37)
			);
			var simpleMarker = null;
			if(clusterRequest=='Enable')
			{
				simpleMarker = new google.maps.Marker({position: pos, title: result["name"] + "|" + result["type"], icon:m_icon});
			} else {
				simpleMarker = new google.maps.Marker({position: pos, title: result["name"] + "|" + result["type"], map:map, icon:m_icon});
			}
			var infowindow = new google.maps.InfoWindow;
			bindInfoW(simpleMarker, contentString, infowindow);
			simpleMarker.set("map_value",result["map_value"]);
			simpleMarker.set("map_name",result["name"]);
			simpleMarker.set("map_id",result["record_id"]);
			local_markersArray.push(simpleMarker);
		}
		if( type_or_valueRequest != 'type')
		{
			var circleItem = getCircle2(result["map_value"],pos,result["name"],contentString);
			local_circleArray.push(circleItem);
		}
        // danzi.tn@20150618 heatMap
        map_value_10 = result["map_value"]/10;
        var heatMapItem =  {location: pos, weight: map_value_10};
        var agent = {user_name:result["user_name"],last_name:result["last_name"], first_name:result["first_name"]};
        var convexHull = null;
        if( result["user_name"] in convexHullSet ) {
            convexHull = convexHullSet[result["user_name"]];
            convexHull.addPoint(pos.lng(), pos.lat(), agent);
        } else {
            convexHull = new ConvexHullGrahamScan();
            convexHull.addPoint(pos.lng(), pos.lat(), agent);
            convexHullSet[result["user_name"]] = convexHull;
        }
        if( maxInt < map_value_10) maxInt = map_value_10;
        heatMapData.push(heatMapItem);
	}
    maxInt = maxInt*0.5;
}

function bindInfoW(marker, contentString, infowindow)
{
		google.maps.event.clearListeners(marker, 'click');
        google.maps.event.addListener(marker, 'click', function() {
            infowindow.setContent(contentString);
            infowindow.open(map, marker);
        });
}

function clearMapCluster() {
	if (markerCluster) {
        	markerCluster.clearMarkers();
    }
}

function showMapCluster() {
	markerCluster = new MarkerClusterer(map, local_markersArray);
}

function numberToCurrency(number) {

    var currencySymbol     = '€';
    var thousandsSeparator = ',';

    number = stripDollarSign(number);
    number = isNaN(number) || number == '' || number == null ? 0.00 : number;
    var numberStr = parseFloat(number).toFixed(2).toString();
    var numberFormatted = new Array(numberStr.slice(-3));   // this returns the decimal and cents
    numberStr = numberStr.substring(0, numberStr.length-3); // this removes the decimal and cents
    /*
     * Why is there an `unshift()` function, but no `shift()`?
     * Also, a `pop()` function would be handy here.
     */
    while (numberStr.length > 3) {
        numberFormatted.unshift(numberStr.slice(-3)); // this prepends the last three digits to `numberFormatted`
        numberFormatted.unshift(thousandsSeparator); // this prepends the thousandsSeparator to `numberFormatted`
        numberStr = numberStr.substring(0, numberStr.length-3);  // this removes the last three digits
    }
    numberFormatted.unshift(numberStr); // there are less than three digits in numberStr, so prepend them
    if(module!="HelpDesk") numberFormatted.unshift(currencySymbol); // prepend the currencySymbol

    return numberFormatted.join(''); // put it all together
}

function stripDollarSign(s) {
    if (typeof s == 'string') { s = s.replace(/\€/g, ''); }
    return s;
}

function getCircle2(mapValue, pos, name, desc)
{		
	var displayRadius = Math.log( mapValue + 0.0 ) * 1200;
	//if (mapValue < 10) displayRadius = Math.log(10)*800;
	switch(module) // TODO: qui bisogna decidere se allaragre solo il raggio dei nodi in caso di Ordini di Venita o anche il valore in ingresso map_value
	{
		case "Accounts": 
			displayRadius = displayRadius;
			break;
		case "SalesOrder": 
			displayRadius = displayRadius*2;
			break;
		case "Leads": 
			displayRadius = displayRadius*3;
			break;
	}
	var nStrokeOpacity = 0.8;
	var nFillOpacity = 0.35;
	var fillColorForNumbers = "#D6FF2F";
	var sStrokeColor = "#9F9745";
	if( mapValue < 2000 ) 
	{
		fillColorForNumbers = "#D6FF2F";
		// sStrokeColor="#00AF07";
        sStrokeColor="#000";
	}
	else if( mapValue >= 2000 &&  mapValue < 4000 )
	{
		fillColorForNumbers = "#00AF07";
		// sStrokeColor="#00ECFF";
        sStrokeColor="#000";
	}
	else if( mapValue >= 4000 &&  mapValue < 6000 )
	{
		fillColorForNumbers = "#00ECFF";
		// sStrokeColor = "#0069BF";
        sStrokeColor="#000";
	}
	else if( mapValue >= 6000 &&  mapValue < 8000 )
	{
		fillColorForNumbers = "#0069BF";
		// sStrokeColor = "#FFA200";
        sStrokeColor="#000";
	}
	else if( mapValue >= 8000 &&  mapValue < 10000 )
	{
		fillColorForNumbers = "#FFA200";
		nFillOpacity = 0.7;
		// sStrokeColor = "#FF2A00";
        sStrokeColor="#000";
	}
	else if( mapValue >= 10000 )
	{
		fillColorForNumbers = "#FF2A00";
		nStrokeOpacity = 0.9;
		nFillOpacity = 0.8;
		//sStrokeColor = "#6F390D";
        sStrokeColor="#000";
        displayRadius = 15500.0+0.0;
	}
	var circleOptions = {
		strokeColor: sStrokeColor,
		strokeOpacity: nStrokeOpacity,
		strokeWeight: 2,
		fillColor: fillColorForNumbers,
		fillOpacity: nFillOpacity,
		map: map,
		center: pos,
		radius: displayRadius
	};
	var entityCircle = new google.maps.Circle(circleOptions);
	entityCircle.set("map_value", mapValue);
	var infowindow = new google.maps.InfoWindow({
    		content: desc
	});
	infowindow.setPosition(entityCircle.getCenter());
	google.maps.event.addListener(entityCircle, 'click', function() {
				infowindow.open(map);
	});
	return entityCircle;
}


function initializeSlider() {
	
}

function updateMap() {
	var amountrange = document.getElementById('amountrange');
	amountrange_value = amountrange.value;
	var currentval = $("#amountrange").val();
	var currentval_splitted = currentval.split('-');
	minval = 0;
	maxval = 999000000;
	if( currentval_splitted.length > 1 ) {
		minval = parseInt(currentval_splitted[0]);
		maxval = parseInt(currentval_splitted[1]); // 100000000
        if( maxval == 250000 ) maxval = 999000000;
	}
	fusion_value = amount;
	checkMap();
}

