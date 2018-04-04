<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Missioncontrol
 * @author     Ninad Ramade <ninad.ramade@gmail.com>
 * @copyright  2016 Ninad Ramade
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined ( '_JEXEC' ) or die ();

//JHtml::_ ( 'behavior.keepalive' );
//JHtml::_ ( 'behavior.tooltip' );
//JHtml::_ ( 'behavior.formvalidation' );
JHtml::_ ( 'formbehavior.chosen', 'select' );
JHTML::_ ( 'behavior.calendar' );
// Load admin language file
$lang = JFactory::getLanguage ();
$lang->load ( 'com_landlord', JPATH_SITE );
$doc = JFactory::getDocument ();
$doc->addScript ( JUri::base () . '/media/com_landlord/js/form.js' );
?>
   <style>
       #map {
        height: 700px;
        width: 100%;
       }
       .gm-style
       {
       	color:black;
       }
       .gm-style-iw
       {
       	font-size:12px!important;
       }
       .cost_div
       {
       	float:left;
       }
       .rent_div
       {
       	float:left;
       }
       .buy_div
       {
       		float: left;
    		width: 100%;
    		text-align: center;
       }
       .cost_table
       {
       		font-weight:bold;
       }
       .cost_table td
       {
       		padding: 2px 14px 2px 0px;
       }
       .slider_label
       {
       	text-align:center;
       	margin-top:5px;
       }
       .slider
       {
       	width:100%;
       	margin-bottom:10px;
    	background-image: -webkit-gradient(linear, 0% 0%, 100% 0%, color-stop(1%, #ddd), color-stop(1%, #ddd));
       }
       .disabled_buy
       {
       	background-color:lightgrey;
       	color:black;
       	cursor:default;
       }
       .disabled_buy:link
       {
       	background-color:lightgrey;
       	color:black;
       }
       .disabled_buy:visited
       {
       	background-color:lightgrey;
       	color:black;
       }
       .disabled_buy:hover
       {
       	background-color:lightgrey;
       	color:black;
       }
       .disabled_buy:active
       {
       	background-color:lightgrey;
       	color:black;
       }
       input[type=range]{
    -webkit-appearance: none;
}

input[type=range]::-webkit-slider-runnable-track {
    width: 300px;
    height: 5px;
    border: none;
    border-radius: 3px;
    cursor:pointer;
}

input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
    border: none;
    height: 22px;
    width: 22px;
    border-radius:50%;
    background: #4c0120;
    margin-top: -9px;
    cursor:pointer;
}

input[type=range]:focus {
    outline: none;
}
    </style>
<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCTbismgZ1tAeVHX5Dn9OaF3IRtxz_FPVY&libraries=places"></script>

<script type="text/javascript">
var map;
var activeWindow;

function tryAPIGeolocation()
{
	 jQuery.post( "https://www.googleapis.com/geolocation/v1/geolocate?key=AIzaSyCTbismgZ1tAeVHX5Dn9OaF3IRtxz_FPVY", function(success) {
		 showPosition({coords: {latitude: success.location.lat, longitude: success.location.lng}});
	  })
	  .fail(function(err) {
	    alert("API Geolocation error! \n\n"+err);
	  });
}
function buyProperty(place_id,place_type,rating)
{
	var r=confirm("Are you sure you want to buy this property?");
	if(r==true)
	{
		var percent=jQuery("#slider_"+place_id).val();
		jQuery.ajax({
			type: 'POST',
		    dataType: 'json',
			data:{"place_id":place_id,"place_type":place_type,"rating":rating,"percent":percent},
			url: "index.php?option=com_landlord&task=properties.buyproperty", 
			success: function(result){
				alert("Property purchased successfully!");
				location.reload();
			}
		});
	}
}
function changeSlider(place_id,place_type,rating)
{
	    var val = jQuery('#slider_'+place_id).val()*1;
	    var prev_val = jQuery('#prev_slider_'+place_id).val()*1;
	    jQuery('#slider_'+place_id).siblings(".slider_label").text(val+'%');
	    var cost=jQuery("#cost_"+place_id).text();
	    cost=cost.substr(0,cost.indexOf(' '))*1;
	    var rent=jQuery("#rent_"+place_id).text();
	    rent=rent.substr(0,rent.indexOf(' '))*1;
	    var charges=jQuery("#charges_"+place_id).text();
	    charges=charges.substr(0,charges.indexOf(' '))*1;
	    var profit=jQuery("#profit_"+place_id).text();
	    profit=profit.substr(0,profit.indexOf(' '))*1;
	    cost=(cost/prev_val)*val;
	    rent=(rent/prev_val)*val;
	    charges=(charges/prev_val)*val;
	    profit=(profit/prev_val)*val;
	    jQuery("#cost_"+place_id).text(cost+' PRC');
	    jQuery("#rent_"+place_id).text(rent+' PRC');
	    jQuery("#charges_"+place_id).text(charges+' PRC');
	    jQuery("#profit_"+place_id).text(profit+' PRC');
	    if(cost><?php echo $this->portfolio->wallet_balance; ?>)
	    {
	    	jQuery("#buy_button_"+place_id).addClass("disabled_buy");
	    	jQuery("#buy_button_"+place_id).removeAttr("onclick");
	    }
	    else
	    {
	    	jQuery("#buy_button_"+place_id).removeClass("disabled_buy");
	    	jQuery("#buy_button_"+place_id).attr("onclick","buyProperty('"+place_id+"','"+place_type+"',"+rating+")");
	    }
	    jQuery('#prev_slider_'+place_id).val(val);
}
function createMarker(place) {
    var placeLoc = place.geometry.location;
    var rating=0;
    var marker = new google.maps.Marker({
      map: map,
      position: place.geometry.location
    });
    var infowindow = new google.maps.InfoWindow({ maxWidth: 340 });
    google.maps.event.addListener(marker, 'click', function() {
    	if (activeWindow) {
    		activeWindow.close();
        }
		rating=typeof place.rating!="undefined" ? place.rating : 1;
      infowindow.setContent('<strong>'+place.name+'</strong><br/>'+place.vicinity+'<br/><div class="buy_wrapper" id="'+place.place_id+'"><table class="cost_table"><tr><td>Cost</td><td>Daily Rent</td><td>Daily Charges</td><td>Daily Profit</td></tr><tr><td id="cost_'+place.place_id+'"><img src="<?php echo JURI::root().'components/com_landlord/assets/loader.svg';?>"/></td><td id="rent_'+place.place_id+'"><img src="<?php echo JURI::root().'components/com_landlord/assets/loader.svg';?>"/></td><td id="charges_'+place.place_id+'"><img src="<?php echo JURI::root().'components/com_landlord/assets/loader.svg';?>"/></td><td id="profit_'+place.place_id+'"><img src="<?php echo JURI::root().'components/com_landlord/assets/loader.svg';?>"/></td></tr></table><div class="slider_div"><label class="slider_label" for="slider_'+place.place_id+'">1%</label><input type="hidden" id="prev_slider_'+place.place_id+'" value="1" /><input type="range" min="1" max="100" value="1" class="slider" oninput="changeSlider(\''+place.place_id+'\',\''+place.types[0]+'\',\''+rating+'\')" id="slider_'+place.place_id+'"></div><div class="buy_div"><a href="javascript:void()" id="buy_button_'+place.place_id+'" class="btn btn-primary disabled_buy">BUY</a></div></div>');
      activeWindow=infowindow;
      infowindow.open(map, this);

      //ajax call
      jQuery.ajax({
			type: 'POST',
		    dataType: 'json',
			data:{"place_type":place.types[0],"rating":rating},
			url: "index.php?option=com_landlord&task=properties.getcost", 
			success: function(result){
				jQuery("#cost_"+place.place_id).text((result.cost)+" PRC");
				jQuery("#rent_"+place.place_id).text((result.rent)+" PRC");
				jQuery("#charges_"+place.place_id).text((result.charges)+" PRC");
				jQuery("#profit_"+place.place_id).text((result.rent-result.charges)+" PRC");
				if(result.cost<=<?php echo $this->portfolio->wallet_balance; ?>)
				{
					jQuery("#buy_button_"+place.place_id).removeClass("disabled_buy");
					jQuery("#buy_button_"+place.place_id).attr("onclick","buyProperty('"+place.place_id+"','"+place.types[0]+"',"+rating+")");
				}
			}
		});
    });
  }
function plotMarkers(results, status, pagination)
{
	console.log(pagination);
	if (status == google.maps.places.PlacesServiceStatus.OK) {
	    for (var i = 0; i < results.length; i++) {
	      var place = results[i];
	      createMarker(results[i]);
	    }
	  }
}
function showPosition(position)
{
	  var styles = {
      default: null,
      hide: [
        {
            elementType: 'labels.icon',
          	stylers: [{visibility: 'off'}]
        }
      ]
    };
	    var current_loc=new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
	  map = new google.maps.Map(document.getElementById('map'), {
	    zoom: 12,
	    center:current_loc,
	    mapTypeId: google.maps.MapTypeId.ROADMAP,
	    gestureHandling:'greedy'
	  });
	  map.setOptions({styles: styles['hide']});

	  var request = {
			    location: current_loc,
			    radius: '1000'
			  };

			  service = new google.maps.places.PlacesService(map);
			  service.nearbySearch(request, plotMarkers);
}
function showPositionFallback(error)
{
	switch (error.code) {
    case error.TIMEOUT:
      alert("Browser geolocation error !\n\nTimeout.");
      break;
    case error.PERMISSION_DENIED:
      if(error.message.indexOf("Only secure origins are allowed") == 0) {
        tryAPIGeolocation();
      }
      break;
    case error.POSITION_UNAVAILABLE:
      alert("Browser geolocation error !\n\nPosition unavailable.");
      break;
  }
}
jQuery(document).ready(function(){
	if (navigator.geolocation) {
    	navigator.geolocation.getCurrentPosition(showPosition,showPositionFallback);
	}
}); 
</script>

<div id="map"></div>
