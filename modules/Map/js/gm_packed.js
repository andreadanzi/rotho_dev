function initialize(){directionsDisplay=new google.maps.DirectionsRenderer;var e="VTStyle";var t={zoom:6,center:home_center,mapTypeControlOptions:{mapTypeIds:[e,google.maps.MapTypeId.ROADMAP,google.maps.MapTypeId.SATELLITE,google.maps.MapTypeId.TERRAIN]},mapTypeId:e,streetViewControl:true};var n=[{featureType:"poi",elementType:"all",stylers:[{visibility:"off"}]},{featureType:"landscape.natural",elementType:"all",stylers:[{visibility:"simplified"},{hue:"#0C4F00"}]},{featureType:"road",elementType:"all",stylers:[{visibility:"off"}]},{featureType:"landscape.man_made",elementType:"all",stylers:[{visibility:"simplified"},{hue:"#EF2C2C"}]},{featureType:"administrative",elementType:"geometry",stylers:[{hue:"#0088ff"},{saturation:80},{lightness:-17}]}];map=new google.maps.Map(document.getElementById("map_canvas"),t);var r={map:map,name:"VT Style"};var i=new google.maps.StyledMapType(n,r);directionsDisplay.setMap(map);directionsDisplay.setPanel(document.getElementById("route"));map.mapTypes.set(e,i);map.setMapTypeId(e);checkMap("ND");initializeSlider();var s=document.getElementById("type1");google.maps.event.addDomListener(s,"click",function(){checkMap(this.id)});s=document.getElementById("type2");google.maps.event.addDomListener(s,"click",function(){checkMap(this.id)});s=document.getElementById("type3");google.maps.event.addDomListener(s,"click",function(){checkMap(this.id)});s=document.getElementById("clust1");google.maps.event.addDomListener(s,"click",function(){checkMap(this.id)});s=document.getElementById("clust2");google.maps.event.addDomListener(s,"click",function(){checkMap(this.id)});s=document.getElementById("valueSel");domElementND=document.getElementById("valueSelND");domElementPROD=document.getElementById("valueSelPROD");google.maps.event.addDomListener(s,"click",function(){updateValueFilterContainer(this)});google.maps.event.addDomListener(domElementND,"click",function(){updateValueFilterContainer(this)});google.maps.event.addDomListener(domElementPROD,"click",function(){updateValueFilterContainer(this)})}function clearClusters(e){e.preventDefault();e.stopPropagation();markerCluster.clearMarkers()}function getMarker2(e,t,n,r,i,s){var o=new google.maps.InfoWindow({content:r});var u=new google.maps.MarkerImage(i,new google.maps.Size(32,37),new google.maps.Point(0,0),new google.maps.Point(16,37));var a=new google.maps.MarkerImage("modules/Map/img/shadow.png",new google.maps.Size(54,37),new google.maps.Point(0,0),new google.maps.Point(16,37));var f={coord:[29,0,30,1,31,2,31,3,31,4,31,5,31,6,31,7,31,8,31,9,31,10,31,11,31,12,31,13,31,14,31,15,31,16,31,17,31,18,31,19,31,20,31,21,31,22,31,23,31,24,31,25,31,26,31,27,31,28,31,29,30,30,29,31,23,32,22,33,21,34,20,35,19,36,12,36,11,35,10,34,9,33,8,32,2,31,1,30,0,29,0,28,0,27,0,26,0,25,0,24,0,23,0,22,0,21,0,20,0,19,0,18,0,17,0,16,0,15,0,14,0,13,0,12,0,11,0,10,0,9,0,8,0,7,0,6,0,5,0,4,0,3,0,2,1,1,2,0,29,0],type:"poly"};var l=null;if(clusterRequest=="Enable"){l=new google.maps.Marker({position:e,icon:u,shadow:a,shape:f,title:t+"|"+n+"|"+s})}else{l=new google.maps.Marker({position:e,map:map,icon:u,shadow:a,shape:f,title:t+"|"+n+"|"+s})}l.set("map_value",s);google.maps.event.addListener(l,"click",function(){o.open(map,l)});return l}function getDescription(e,t,n,r,i,s,o,u){var a="";switch(module){case"Accounts":a+="<br/><b><span style='float: right'><a href='index.php?module="+module+"&action=DetailView&record="+e+"'>"+n+"</a></b>";break;case"Leads":a+="<br/><b><span style='float: right'><a href='index.php?module="+module+"&action=DetailView&record="+e+"'>"+n+"</a></b>";break;case"SalesOrder":a+="<br/><b><span style='float: right'><a href='index.php?action=CallRelatedList&module=Accounts&selected_header=Sales Order&relation_id=4&record="+e+"'>"+n+"</a></b>";break;case"HelpDesk":a+="<br/><b><span style='float: right'><a href='index.php?action=CallRelatedList&module=Contacts&selected_header=HelpDesk&relation_id=21&record="+e+"'>"+n+"</a></b>";break;case"Potentials":a+="<br/><b><span style='float: right'><a href='index.php?action=CallRelatedList&module=Accounts&selected_header=Potentials&relation_id=2&record="+e+"'>"+n+"</a></b>";break}if(r){a+="<br/>"+r}if(i){a+="<br/>"+numberToCurrency(i)}if(u){}if(o){a+="<br/><br/>"+o}a+="<br/><a onClick='loadDirectionFrom(\""+t.lat()+","+t.lng()+'","'+n+'","'+s+"\")' href='javascript:void(0)'>"+from_lbl+" "+n+"</a>";a+="<br/><a onClick='loadDirectionTo(\""+t.lat()+","+t.lng()+'","'+n+'","'+s+"\")' href='javascript:void(0)'>"+to_lbl+" "+n+"</a>";a+="<span style='float: right'><a href='index.php?module=Map&file=update&action=MapAjax&id="+e+"&show="+module+"'>"+reload_lbl+"</a></span>";return a}function getMarkerFromResults(e,t){if(e){var n="modules/Map/img/symbol_pi.png";var r="OK";if(module=="Leads"){var i=e.substring(0,1).toLowerCase();n="modules/Map/img/letter_"+i+".png"}else{switch(e){case"Reseller":n="modules/Map/img/letter_r.png";if(t==r)n="modules/Map/img/letter_r-p.png";break;case"Competitor":n="modules/Map/img/letter_c.png";if(t==r)n="modules/Map/img/letter_c-p.png";break;case"Partner":n="modules/Map/img/letter_p.png";if(t==r)n="modules/Map/img/letter_p-p.png";break;case"Other":n="modules/Map/img/letter_o.png";if(t==r)n="modules/Map/img/letter_o-p.png";break;case"RC / CARP":n="modules/Map/img/rc-carp.png";if(t==r)n="modules/Map/img/rc-carp-p.png";break;case"RD / DIST":n="modules/Map/img/rd-dist.png";if(t==r)n="modules/Map/img/rd-dist-p.png";break;case"RS / SAFE":n="modules/Map/img/rs-safe.png";if(t==r)n="modules/Map/img/rs-safe-p.png";break;case"RP / PROG":n="modules/Map/img/rp-prog.png";if(t==r)n="modules/Map/img/rp-prog-p.png";break;case"RE / ALTRO":n="modules/Map/img/letter_a.png";if(t==r)n="modules/Map/img/letter_a-p.png";break;case"RC / DIST":n="modules/Map/img/rc-dist.png";if(t==r)n="modules/Map/img/rc-dist-p.png";break;case"RC / PROG":n="modules/Map/img/rc-prog.png";if(t==r)n="modules/Map/img/rc-prog-p.png";break;case"---":n="modules/Map/img/symbol_minus.png";if(t==r)n="modules/Map/img/symbol_minus-p.png";break;case"-":n="modules/Map/img/symbol_minus.png";if(t==r)n="modules/Map/img/symbol_minus-p.png";break;default:n="modules/Map/img/letter_d.png";if(t==r)n="modules/Map/img/letter_d-p.png"}}return n}else{n="modules/Map/img/letter_d.png";if(t==r)n="modules/Map/img/letter_d-p.png";return n}}function loadDirection(e,t,n){to=e;var r={origin:from,destination:to,travelMode:google.maps.DirectionsTravelMode.DRIVING};directionsService.route(r,function(e,t){if(t==google.maps.DirectionsStatus.OK){directionsDisplay.setDirections(e)}});var i=document.getElementById("desc");i.innerHTML=from_lbl+": <span style='font-weight: bold'>"+baseName+" - "+baseCity+"</span> <span style='color:grey; font-size: smaller'>)</span><br/>"+to_lbl+":  <span style='font-weight: bold'>"+t+" - "+n+"</span>"}function loadDirectionFrom(e,t,n){gmfrom=e;document.getElementById("gmfrom").value=gmfrom;document.getElementById("accfrom").value=t+" - "+n;gmto=document.getElementById("gmto").value;accto=document.getElementById("accto").value;if(gmto!="yyy"&&gmfrom!="xxx"){var r={origin:gmfrom,destination:gmto,travelMode:google.maps.DirectionsTravelMode.DRIVING};directionsService.route(r,function(e,t){if(t==google.maps.DirectionsStatus.OK){directionsDisplay.setDirections(e)}});var i=document.getElementById("desc");i.innerHTML=from_lbl+": <span style='font-weight: bold'>"+t+" - "+n+"</span>><br/>"+to_lbl+":  <span style='font-weight: bold'>"+accto+"</span>"}}function loadDirectionTo(e,t,n){gmto=e;document.getElementById("gmto").value=gmto;document.getElementById("accto").value=t+" - "+n;gmfrom=document.getElementById("gmfrom").value;accfrom=document.getElementById("accfrom").value;if(gmto!="yyy"&&gmfrom!="xxx"){var r={origin:gmfrom,destination:gmto,travelMode:google.maps.DirectionsTravelMode.DRIVING};directionsService.route(r,function(e,t){if(t==google.maps.DirectionsStatus.OK){directionsDisplay.setDirections(e)}});var i=document.getElementById("desc");i.innerHTML=from_lbl+": <span style='font-weight: bold'>"+accfrom+"</span><br/>"+to_lbl+":  <span style='font-weight: bold'>"+t+" - "+n+"</span>"}}function restore(){var e=document.getElementById("desc");e.innerHTML="";directionsDisplay.setMap(null);var t=document.getElementById("route");t.innerHTML=""}function clearCircleArray(){if(local_circleArray){for(i in local_circleArray){if(local_circleArray[i]&&typeof local_circleArray[i]=="object"&&typeof local_circleArray[i].setMap=="function"){local_circleArray[i].setVisible(false)}}}}function clearMarkersArray(){if(local_markersArray){for(i in local_markersArray){if(local_markersArray[i]&&typeof local_markersArray[i]=="object"&&typeof local_markersArray[i].setMap=="function"){local_markersArray[i].setVisible(false)}}}}function showCircleArray(){if(local_circleArray){for(i in local_circleArray){if(local_circleArray[i]&&typeof local_circleArray[i]=="object"&&typeof local_circleArray[i].setMap=="function"){var e=local_circleArray[i].get("map_value");if(fusion_value>e||type_or_valueRequest=="type"){local_circleArray[i].setVisible(false)}else{local_circleArray[i].setVisible(true)}}}}}function showMarkersArray(){if(local_markersArray){for(i in local_markersArray){if(local_markersArray[i]&&typeof local_markersArray[i]=="object"&&typeof local_markersArray[i].setMap=="function"){var e=local_markersArray[i].get("map_value");if(fusion_value>e||type_or_valueRequest=="value"){local_markersArray[i].setVisible(false)}else{local_markersArray[i].setVisible(true)}}}}}function deleteCircleArray(){if(local_circleArray&&local_circleArray.length>0){for(i in local_circleArray){if(local_circleArray[i]&&typeof local_circleArray[i]=="object"&&typeof local_circleArray[i].setMap=="function"){local_circleArray[i].setMap(null)}}local_circleArray.length=0;local_circleArray=[]}}function deleteMarkersArray(){if(local_markersArray&&local_markersArray.length>0){for(i in local_markersArray){if(local_markersArray[i]&&typeof local_markersArray[i]=="object"&&typeof local_markersArray[i].setMap=="function"){local_markersArray[i].setMap(null)}}local_markersArray.length=0;local_markersArray=[]}}function checkMap(e){clearMapCluster();deleteCircleArray();deleteMarkersArray();if(e=="clust2")clusterRequest="Disable";if(e=="clust1")clusterRequest="Enable";if(e=="type1")type_or_valueRequest="type";if(e=="type2")type_or_valueRequest="value_and_type";if(e=="type3")type_or_valueRequest="value";createArrays();if(clusterRequest=="Enable"){showMapCluster()}}function createArrays(){for(var e in resultLayer){var t=resultLayer[e];if(fusion_value>t["map_value"])continue;var n=new google.maps.LatLng(t["pos"][0],t["pos"][1]);var r=getDescription(e,n,t["name"],t["type"],t["map_value"],t["city"],t["extra"],t["map_aurea"]);if(t["city"]=="Chur"){myLat=t["pos"][0];myLon=t["pos"][1]}if(type_or_valueRequest!="value"){var i=getMarkerFromResults(t["type"],t["map_aurea"]);var s=new google.maps.MarkerImage(i,new google.maps.Size(32,37),new google.maps.Point(0,0),new google.maps.Point(16,37));var o=null;if(clusterRequest=="Enable"){o=new google.maps.Marker({position:n,title:t["name"]+"|"+t["type"],icon:i})}else{o=new google.maps.Marker({position:n,title:t["name"]+"|"+t["type"],map:map,icon:i})}var u=new google.maps.InfoWindow;bindInfoW(o,r,u);o.set("map_value",t["map_value"]);local_markersArray.push(o)}if(type_or_valueRequest!="type"){var a=getCircle2(t["map_value"],n,t["name"],r);local_circleArray.push(a)}}}function bindInfoW(e,t,n){google.maps.event.addListener(e,"click",function(){n.setContent(t);n.open(map,e)})}function clearMapCluster(){if(markerCluster){markerCluster.clearMarkers()}}function showMapCluster(){markerCluster=new MarkerClusterer(map,local_markersArray)}function numberToCurrency(e){var t="�";var n=",";e=stripDollarSign(e);e=isNaN(e)||e==""||e==null?0:e;var r=parseFloat(e).toFixed(2).toString();var i=new Array(r.slice(-3));r=r.substring(0,r.length-3);while(r.length>3){i.unshift(r.slice(-3));i.unshift(n);r=r.substring(0,r.length-3)}i.unshift(r);if(module!="HelpDesk")i.unshift(t);return i.join("")}function stripDollarSign(e){if(typeof e=="string"){e=e.replace(/\�/g,"")}return e}function getCircle2(e,t,n,r){var i=Math.log(e)*400;if(e<10)i=Math.log(10)*400;switch(module){case"Accounts":i=i;break;case"SalesOrder":i=i*2;break;case"Leads":i=i*3;break}var s=.7;var o=.35;var u="#D6FF2F";var a="#9F9745";if(e<1e3){u="#D6FF2F";a="#00AF07";s=.5}else if(e>=1e3&&e<1e4){u="#00AF07";a="#00ECFF";s=.6}else if(e>=1e4&&e<1e5){u="#00ECFF";a="#0069BF"}else if(e>=1e5&&e<1e6){u="#0069BF";a="#FFA200"}else if(e>=1e6&&e<1e7){u="#FFA200";s=.8;o=.7;a="#FF2A00"}else if(e>=1e7){u="#FF2A00";s=.9;o=.8;a="#6F390D"}var f={strokeColor:a,strokeOpacity:s,strokeWeight:2,fillColor:u,fillOpacity:o,map:map,center:t,radius:i};var l=new google.maps.Circle(f);l.set("map_value",e);var c=new google.maps.InfoWindow({content:r});c.setPosition(l.getCenter());google.maps.event.addListener(l,"click",function(){c.open(map)});return l}function initializeSlider(){var e=document.getElementById("slider");slider=new goog.ui.Slider;slider.decorate(e);slider.setMaximum(400);slider.setStep(1);slider.addEventListener(goog.ui.Component.EventType.CHANGE,function(){if(sliderTimer)window.clearTimeout(sliderTimer);sliderTimer=window.setTimeout(updateMap,500);var e=sliderValueToAmount(slider.getValue());e=e*1e3;var t="#D6FF2F";if(e<1e3){t="#D6FF2F"}else if(e>=1e3&&e<1e4){t="#00AF07"}else if(e>=1e4&&e<1e5){t="#00ECFF"}else if(e>=1e5&&e<1e6){t="#0069BF"}else if(e>=1e6&&e<1e7){t="#FFA200"}else if(e>=1e7){t="#FF2A00"}document.getElementById("slider-value").innerHTML="<span style='font-weight: bold; color:"+t+";'>"+Math.round(slider.getValue()*slider.getValue()/10)+" k </span>"});slider.setValue(0)}function updateMap(){var e=sliderValueToAmount(slider.getValue());fusion_value=e*1e3;checkMap()}function sliderValueToAmount(e){return Math.round(e*e/10)}var local_markersArray=[];var local_circleArray=[];var markerCluster=null;var clust_markers=[];var map=null;var fusion_value=0;var directionsDisplay=null